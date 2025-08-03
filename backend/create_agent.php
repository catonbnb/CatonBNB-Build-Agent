<?php
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "your username";
$password = "your database pw";
$dbname = "your database name";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage(), 3, "errors.log");
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Function to make Together AI API call (optional)
function callTogetherAI($name, $character, $skill) {
    $apiKey = getenv('TOGETHER_AI_API_KEY') ?: 'YOUR_TOGETHER_AI_API_KEY';
    $endpoint = 'https://api.together.ai/v1/agents';

    $data = [
        'name' => $name,
        'character' => $character,
        'skill' => $skill
    ];

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        error_log("Together AI API error: " . $error, 3, "errors.log");
        return ['success' => false, 'message' => 'API request failed: ' . $error];
    }

    if ($httpCode !== 200) {
        error_log("Together AI API failed with status $httpCode: " . $response, 3, "errors.log");
        return ['success' => false, 'message' => 'API request failed with status ' . $httpCode];
    }

    return ['success' => true, 'data' => json_decode($response, true)];
}

// Handle requests
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $wallet_address = $_POST['wallet_address'] ?? '';
    $action = $_POST['action'] ?? '';

    if (empty($wallet_address)) {
        echo json_encode(['success' => false, 'message' => 'Wallet address is required']);
        exit;
    }

    if ($action === 'edit' && isset($_POST['agent_id'])) {
        // Handle edit agent
        $agent_id = $_POST['agent_id'];
        $name = $_POST['name'] ?? '';
        $character = $_POST['character'] ?? '';
        $skill = $_POST['skill'] ?? '';

        if (empty($name) || empty($character) || empty($skill)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            exit;
        }

        // Validate ENUM values
        $valid_characters = ['friendly', 'professional'];
        $valid_skills = ['doctor', 'assistant', 'accounting', 'pr', 'crypto_analyst'];
        if (!in_array($character, $valid_characters) || !in_array($skill, $valid_skills)) {
            echo json_encode(['success' => false, 'message' => 'Invalid character or skill']);
            exit;
        }

        // Handle image upload
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../Uploads/';
            $image_name = uniqid() . '_' . basename($_FILES['image']['name']);
            $image_path = $upload_dir . $image_name;

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                error_log("File upload failed for $image_name", 3, "errors.log");
                echo json_encode(['success' => false, 'message' => 'Failed to upload image']);
                exit;
            }
            $image = "Uploads/" . $image_name;
        }

        // Update agent in database
        try {
            $sql = "UPDATE agents SET name = :name, `character` = :character, skill = :skill" . ($image ? ", image = :image" : "") . " WHERE id = :id AND wallet_address = :wallet_address";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':character', $character);
            $stmt->bindParam(':skill', $skill);
            $stmt->bindParam(':id', $agent_id);
            $stmt->bindParam(':wallet_address', $wallet_address);
            if ($image) {
                $stmt->bindParam(':image', $image);
            }
            $stmt->execute();
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            error_log("Database update failed: " . $e->getMessage(), 3, "errors.log");
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        // Check agent limit before creating
        try {
            $stmt = $conn->prepare("SELECT COUNT(*) as agent_count FROM agents WHERE wallet_address = :wallet_address");
            $stmt->bindParam(':wallet_address', $wallet_address);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result['agent_count'] >= 3) {
                echo json_encode(['success' => false, 'message' => 'Maximum 3 agents allowed per wallet']);
                exit;
            }
        } catch (PDOException $e) {
            error_log("Agent count check failed: " . $e->getMessage(), 3, "errors.log");
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
            exit;
        }

        // Handle create agent
        $name = $_POST['name'] ?? '';
        $character = $_POST['character'] ?? '';
        $skill = $_POST['skill'] ?? '';

        if (empty($name) || empty($character) || empty($skill) || !isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'All fields and image are required']);
            exit;
        }

        // Validate ENUM values
        $valid_characters = ['friendly', 'professional'];
        $valid_skills = ['doctor', 'assistant', 'accounting', 'pr', 'crypto_analyst'];
        if (!in_array($character, $valid_characters) || !in_array($skill, $valid_skills)) {
            echo json_encode(['success' => false, 'message' => 'Invalid character or skill']);
            exit;
        }

        // Call Together AI API (optional)
        $apiResult = ['success' => true];
        if (getenv('TOGETHER_AI_API_KEY') || defined('TOGETHER_AI_API_KEY')) {
            $apiResult = callTogetherAI($name, $character, $skill);
            if (!$apiResult['success']) {
                error_log("Proceeding with database insertion despite API failure: " . $apiResult['message'], 3, "errors.log");
            }
        } else {
            error_log("No Together AI API key provided, skipping API call", 3, "errors.log");
        }

        // Handle image upload
        $upload_dir = '../Uploads/';
        $image_name = uniqid() . '_' . basename($_FILES['image']['name']);
        $image_path = $upload_dir . $image_name;

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
            error_log("File upload failed for $image_name", 3, "errors.log");
            echo json_encode(['success' => false, 'message' => 'Failed to upload image']);
            exit;
        }

        $display_path = "Uploads/" . $image_name;

        // Save to database and update points
        try {
            $conn->beginTransaction();

            // Insert agent
            $sql = "INSERT INTO agents (wallet_address, image, name, `character`, skill) VALUES (:wallet_address, :image, :name, :character, :skill)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':wallet_address', $wallet_address);
            $stmt->bindParam(':image', $display_path);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':character', $character);
            $stmt->bindParam(':skill', $skill);
            $stmt->execute();

            // Insert or update Agent_user points
            $sql = "INSERT INTO Agent_user (wallet_address, points) VALUES (:wallet_address, 100) 
                    ON DUPLICATE KEY UPDATE points = points + 100, updated_at = CURRENT_TIMESTAMP";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':wallet_address', $wallet_address);
            $stmt->execute();

            $conn->commit();
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            $conn->rollBack();
            error_log("Database operation failed: " . $e->getMessage(), 3, "errors.log");
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }
} elseif ($action === 'list' && isset($_GET['wallet_address'])) {
    // List agents and points
    $wallet_address = $_GET['wallet_address'];
    try {
        // Fetch agents
        $stmt = $conn->prepare("SELECT id, image, name, `character`, skill FROM agents WHERE wallet_address = :wallet_address");
        $stmt->bindParam(':wallet_address', $wallet_address);
        $stmt->execute();
        $agents = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch points
        $stmt = $conn->prepare("SELECT points FROM Agent_user WHERE wallet_address = :wallet_address");
        $stmt->bindParam(':wallet_address', $wallet_address);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $points = $user ? $user['points'] : 0;

        // Calculate remaining agents
        $agent_count = count($agents);
        $remaining_agents = 3 - $agent_count;

        echo json_encode([
            'success' => true,
            'agents' => $agents,
            'points' => $points,
            'remaining_agents' => $remaining_agents
        ]);
    } catch (PDOException $e) {
        error_log("Database query failed: " . $e->getMessage(), 3, "errors.log");
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}

$conn = null;
?>