<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CatonBNB - AI Agent Creator</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="infra/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="shortcut icon" type="image/x-icon" href="https://catonbnb.com/assets/logos.webp">
    <meta id="og-type" property="og:type" content="website" />
    <meta id="og-url" property="og:url" content="https://catonbnb.com/" />
    <meta id="og-image" property="og:image" content="https://catonbnb.com/assets/thumb5.png" />
    <meta id="og-description" property="og:description" content="Welcome to new era — where humanity and AGI live side by side.  build on bnbchain" />
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="https://catonbnb.com/">
    <meta name="twitter:title" content="CatonBNB - AI Agent Creator">
    <meta name="twitter:description" content="Welcome to new era — where humanity and AGI live side by side.  build on bnbchain ">
    <meta name="twitter:image" content="https://catonbnb.com/assets/thumb5.png">
    <meta name="twitter:site" content="@catonbnb">
    <meta name="twitter:creator" content="@catonbnb">
    <script src="https://cdn.jsdelivr.net/npm/web3@1.7.0/dist/web3.min.js"></script>
</head>
<body class="bg-gray-900 text-white">
    <div id="contentWrapper" class="mx-hg">
        <!-- Header -->
        <header class="bg-gray-800 p-4 flex justify-between items-center">
            <div class="flex items-center">
                <img src="https://catonbnb.com/assets/logos.webp" alt="CatonBNB Logo" class="h-10">
            </div>
            <button id="hamburgerBtn" class="md:hidden text-white focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                </svg>
            </button>
            <nav id="navbar" class="hidden md:flex md:items-center">
                <ul class="flex space-x-4">
                    <li><a href="/" class="hover:text-yellow-400">Home</a></li>
                    <li><a href="/news" class="hover:text-yellow-400">Research</a></li>
                    <li><a href="https://catonbnb.gitbook.io" class="hover:text-yellow-400">Docs</a></li>
                </ul>
            </nav>
            <nav id="mobileMenu" class="hidden flex-col bg-gray-800 w-full absolute top-16 left-0 p-4 md:hidden">
                <ul class="space-y-2">
                    <li><a href="/" class="block hover:text-yellow-400">Home</a></li>
                    <li><a href="/news" class="block hover:text-yellow-400">Research</a></li>
                    <li><a href="https://catonbnb.gitbook.io" class="block hover:text-yellow-400">Docs</a></li>
                </ul>
            </nav>
        </header>

        <!-- Main Content -->
        <main class="container mx-auto p-4 sm:p-6 hidden">
            <!-- Stats Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
                <div class="bg-gray-800 p-4 rounded-lg text-center grid-border">
                    <h3 class="text-lg font-bold">Agent Created</h3>
                    <p id="agentCount" class="text-2xl">0</p>
                </div>
                <div class="bg-gray-800 p-4 rounded-lg text-center grid-border">
                    <h3 class="text-lg font-bold">Your Robot</h3>
                    <p id="robotCount" class="text-2xl">0</p>
                </div>
                <div class="bg-gray-800 p-4 rounded-lg text-center grid-border">
                    <h3 class="text-lg font-bold">Point</h3>
                    <p id="pointCount" class="text-2xl">0</p>
                </div>
                <div class="bg-gray-800 p-4 rounded-lg text-center grid-border">
                    <h3 class="text-lg font-bold">Agent Limit</h3>
                    <p id="remainingAgentCount" class="text-2xl">3</p>
                </div>
            </div>

            <!-- Wallet and Create Agent Buttons -->
            <div class="flex justify-end mb-4 space-x-2">
                <button id="connectWallet" class="btn-primary text-white font-bold py-2 px-4 rounded">
                    Connect Wallet
                </button>
                <button id="createAgentBtn" class="btn-primary text-white font-bold py-2 px-4 rounded">
                    Create Agent
                </button>
            </div>

            <!-- Agent List -->
            <div id="agentList" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                <div id="noAgentsMessage" class="col-span-full text-center hidden">
                    <img src="https://news.mintfunnel.co/wp-content/uploads/2025/07/41bc996c50d0ac71b6c0bafe47f59ac3-1536x808.jpg" alt="No Agents" class="no-agents-image" onerror="this.src='assets/no-agents.png'">
                </div>
            </div>
        </main>

        <!-- Footer -->
        <div class="footer">
            &copy; 2025 CatonBNB AGI. Built for the future of intelligent decentralized systems.
            <div class="social-icons">
                <a href="https://x.com/catonbnb" target="_blank" title="Follow us on X"><i class="fa-brands fa-x-twitter"></i></a>
                <a href="https://t.me/catbuildon_bnb" target="_blank" title="Join us on Telegram"><i class="fab fa-telegram"></i></a>
            </div>
        </div>
    </div>

    <!-- Welcome Popup -->
    <div id="welcomePopup" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-gray-800 p-4 sm:p-6 rounded-lg w-full max-w-lg mx-4 welcome-popup popup-content flex grid-border">
            <div class="w-1/2 hidden sm:block">
                <img src="https://catonbnb.com/assets/logos.webp" alt="CatonBNB Logo" class="w-full h-full object-contain">
            </div>
            <div class="w-full sm:w-1/2 flex flex-col justify-center">
                <h2 class="text-xl sm:text-2xl mb-4 text-center">Welcome to CatonBNB AGI</h2>
                <p class="text-center mb-4 text-sm sm:text-base">Connect wallet to start your AI Agent journey. Build, explore, and go viral.</p>
                <div class="flex justify-center gap-2">
                    <button id="welcomeConnectWallet" class="btn-primary text-white py-2 px-4 rounded text-sm">Connect Wallet</button>
                    <a href="/"><button class="btn-primary text-white py-2 px-4 rounded text-sm">Go Home</button></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Agent Popup -->
    <div id="createAgentPopup" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-gray-800 p-4 sm:p-6 rounded-lg w-full max-w-md mx-4 popup-content">
            <h2 class="text-xl sm:text-2xl mb-4 text-center">Create AI Agent</h2>
            <form id="createAgentForm">
                <div class="mb-4">
                    <label class="block mb-2 text-sm sm:text-base">Image</label>
                    <input type="file" name="image" accept="image/*" class="w-full p-2 bg-gray-700 rounded text-sm" required>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-sm sm:text-base">Name</label>
                    <input type="text" name="name" class="w-full p-2 bg-gray-700 rounded text-sm" required>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-sm sm:text-base">Character</label>
                    <select name="character" class="w-full p-2 bg-gray-700 rounded text-sm" required>
                        <option value="friendly">Friendly</option>
                        <option value="professional">Professional</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-sm sm:text-base">Skill</label>
                    <select name="skill" class="w-full p-2 bg-gray-700 rounded text-sm" required>
                        <option value="doctor">Doctor</option>
                        <option value="assistant">Assistant</option>
                        <option value="accounting">Accounting</option>
                        <option value="pr">PR</option>
                        <option value="crypto_analyst">Crypto Analyst</option>
                    </select>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" id="cancelCreate" class="bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded text-sm">Cancel</button>
                    <button type="submit" class="btn-primary text-white py-2 px-4 rounded text-sm">Create</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Disconnect Wallet Popup -->
    <div id="disconnectWalletPopup" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-gray-800 p-4 sm:p-6 rounded-lg w-full max-w-sm mx-4 popup-content">
            <h2 class="text-xl sm:text-2xl mb-4 text-center">Disconnect Wallet?</h2>
            <div class="flex justify-end space-x-2">
                <button id="cancelDisconnect" class="bg-gray-600 hover:bg-gray-700 text-white py-2 px-4 rounded text-sm">Cancel</button>
                <button id="disconnectWallet" class="bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded text-sm">Disconnect</button>
            </div>
        </div>
    </div>

    <!-- Edit Agent Popup -->
    <div id="editAgentPopup" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-gray-800 p-4 sm:p-6 rounded-lg w-full max-w-md mx-4 popup-content">
            <h2 class="text-xl sm:text-2xl mb-4 text-center">Edit AI Agent</h2>
            <form id="editAgentForm">
                <input type="hidden" name="agent_id">
                <div class="mb-4">
                    <label class="block mb-2 text-sm sm:text-base">Image</label>
                    <input type="file" name="image" accept="image/*" class="w-full p-2 bg-gray-700 rounded text-sm">
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-sm sm:text-base">Name</label>
                    <input type="text" name="name" class="w-full p-2 bg-gray-700 rounded text-sm" required>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-sm sm:text-base">Character</label>
                    <select name="character" class="w-full p-2 bg-gray-700 rounded text-sm" required>
                        <option value="friendly">Friendly</option>
                        <option value="professional">Professional</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-sm sm:text-base">Skill</label>
                    <select name="skill" class="w-full p-2 bg-gray-700 rounded text-sm" required>
                        <option value="doctor">Doctor</option>
                        <option value="assistant">Assistant</option>
                        <option value="accounting">Accounting</option>
                        <option value="pr">PR</option>
                        <option value="crypto_analyst">Crypto Analyst</option>
                    </select>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" id="cancelEdit" class="bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded text-sm">Cancel</button>
                    <button type="submit" class="btn-primary text-white py-2 px-4 rounded text-sm">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Success Popup -->
    <div id="successPopup" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-gray-800 p-4 sm:p-6 rounded-lg w-full max-w-sm mx-4 success-popup">
            <h2 class="text-xl sm:text-2xl mb-4 text-center">Agent Created Successfully!</h2>
            <div class="flex justify-center">
                <button id="closeSuccess" class="btn-primary text-white py-2 px-4 rounded text-sm">Close</button>
            </div>
        </div>
    </div>

    <!-- Connect Wallet First Popup -->
    <div id="connectWalletFirstPopup" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-gray-800 p-4 sm:p-6 rounded-lg w-full max-w-sm mx-4 connect-wallet-popup">
            <h2 class="text-xl sm:text-2xl mb-4 text-center">Connect Wallet First</h2>
            <p class="text-center mb-4 text-sm sm:text-base">Please connect your wallet to create or view agents.</p>
            <div class="flex justify-center">
                <button id="closeConnectWallet" class="btn-primary text-white py-2 px-4 rounded text-sm">Close</button>
            </div>
        </div>
    </div>

    <!-- Maximum Agents Reached Popup -->
    <div id="maxAgentsPopup" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-gray-800 p-4 sm:p-6 rounded-lg w-full max-w-sm mx-4 max-agents-popup">
            <h2 class="text-xl sm:text-2xl mb-4 text-center">Maximum 3 Agents Reached</h2>
            <p class="text-center mb-4 text-sm sm:text-base">You have reached the limit of 3 agents per wallet.</p>
            <div class="flex justify-center">
                <button id="closeMaxAgents" class="btn-primary text-white py-2 px-4 rounded text-sm">Close</button>
            </div>
        </div>
    </div>

    <script src="infra/script.js"></script>
<!-- USE company build -->
</body>
</html>