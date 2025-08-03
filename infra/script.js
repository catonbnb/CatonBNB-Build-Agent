document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM fully loaded at', new Date().toLocaleString());
    const connectWalletBtn = document.getElementById('connectWallet');
    const createAgentBtn = document.getElementById('createAgentBtn');
    const createAgentPopup = document.getElementById('createAgentPopup');
    const editAgentPopup = document.getElementById('editAgentPopup');
    const disconnectWalletPopup = document.getElementById('disconnectWalletPopup');
    const successPopup = document.getElementById('successPopup');
    const connectWalletFirstPopup = document.getElementById('connectWalletFirstPopup');
    const maxAgentsPopup = document.getElementById('maxAgentsPopup');
    const welcomePopup = document.getElementById('welcomePopup');
    const welcomeConnectWallet = document.getElementById('welcomeConnectWallet');
    const createAgentForm = document.getElementById('createAgentForm');
    const editAgentForm = document.getElementById('editAgentForm');
    const agentList = document.getElementById('agentList');
    const noAgentsMessage = document.getElementById('noAgentsMessage');
    const hamburgerBtn = document.getElementById('hamburgerBtn');
    const mobileMenu = document.getElementById('mobileMenu');
    const mainContent = document.querySelector('main');
    const contentWrapper = document.getElementById('contentWrapper');
    const agentCount = document.getElementById('agentCount');
    const robotCount = document.getElementById('robotCount');
    const pointCount = document.getElementById('pointCount');
    const remainingAgentCount = document.getElementById('remainingAgentCount');
    let web3, accounts = null;

    // Verify critical elements
    if (!welcomePopup || !welcomeConnectWallet) {
        console.error('Welcome popup elements not found:', { welcomePopup, welcomeConnectWallet });
        alert('Error: Welcome popup not found. Please check index.php.');
        return;
    }
    if (!connectWalletFirstPopup) {
        console.error('connectWalletFirstPopup element not found in DOM');
        alert('Error: Connect Wallet First popup not found. Please check index.php.');
        return;
    }
    if (!maxAgentsPopup) {
        console.error('maxAgentsPopup element not found in DOM');
        alert('Error: Maximum Agents popup not found. Please check index.php.');
        return;
    }
    if (!noAgentsMessage) {
        console.error('noAgentsMessage element not found in DOM');
        alert('Error: No Agents message/image not found. Please check index.php.');
        return;
    }
    if (!agentCount || !robotCount || !pointCount || !remainingAgentCount) {
        console.error('Stats elements not found:', { agentCount, robotCount, pointCount, remainingAgentCount });
        alert('Error: Stats grid elements not found. Please check index.php.');
        return;
    }
    if (!contentWrapper) {
        console.error('contentWrapper element not found in DOM');
        alert('Error: Content wrapper not found. Please check index.php.');
        return;
    }
    console.log('All critical DOM elements verified');

    // Initialize stats
    function updateStats(agentCountValue = 0, points = 0, remainingAgents = 3) {
        console.log('updateStats called with agentCountValue:', agentCountValue, 'points:', points, 'remainingAgents:', remainingAgents);
        agentCount.textContent = agentCountValue;
        robotCount.textContent = 0; // Fixed at 0 as per request
        pointCount.textContent = points;
        remainingAgentCount.textContent = remainingAgents;
    }
    updateStats(); // Set initial values to 0, 0, 3

    // Toggle mobile menu
    hamburgerBtn.addEventListener('click', () => {
        console.log('Hamburger button clicked');
        mobileMenu.classList.toggle('hidden');
    });

    // Initialize Web3 and connect wallet
    async function initWeb3() {
        console.log('initWeb3 called');
        if (!window.ethereum) {
            console.log('MetaMask not detected, showing welcomePopup');
            welcomePopup.classList.remove('hidden');
            contentWrapper.classList.add('blur');
            mainContent.classList.add('hidden');
            return;
        }

        web3 = new Web3(window.ethereum);
        try {
            await window.ethereum.request({
                method: 'wallet_switchEthereumChain',
                params: [{ chainId: '0x38' }],
            });
            accounts = await window.ethereum.request({ method: 'eth_requestAccounts' });
            if (accounts && accounts.length > 0) {
                localStorage.setItem('walletAddress', accounts[0]);
                console.log('Wallet connected:', accounts[0]);
                updateWalletButton();
                welcomePopup.classList.add('hidden');
                contentWrapper.classList.remove('blur');
                mainContent.classList.remove('hidden');
                loadAgents();
            } else {
                throw new Error('No accounts returned from MetaMask.');
            }
        } catch (error) {
            console.error('Wallet connection failed:', error.message);
            welcomePopup.classList.remove('hidden');
            contentWrapper.classList.add('blur');
            mainContent.classList.add('hidden');
            localStorage.removeItem('walletAddress');
            updateWalletButton();
        }
    }

    // Update wallet button text and state
    function updateWalletButton() {
        console.log('updateWalletButton called, accounts:', accounts);
        if (accounts && accounts.length > 0) {
            const shortAddress = `${accounts[0].slice(0, 6)}...${accounts[0].slice(-4)}`;
            connectWalletBtn.textContent = shortAddress;
            createAgentBtn.disabled = false;
            mainContent.classList.remove('hidden');
            welcomePopup.classList.add('hidden');
            contentWrapper.classList.remove('blur');
        } else {
            connectWalletBtn.textContent = 'Connect Wallet';
            createAgentBtn.disabled = false; // Allow clicks to trigger popup
            agentList.innerHTML = '';
            noAgentsMessage.classList.add('hidden');
            mainContent.classList.add('hidden');
            welcomePopup.classList.remove('hidden');
            contentWrapper.classList.add('blur');
            updateStats(); // Reset stats to 0, 0, 3 when disconnected
        }
    }

    // Check cached wallet on page load
    async function checkCachedWallet() {
        console.log('checkCachedWallet called');
        const cachedAddress = localStorage.getItem('walletAddress');
        if (!cachedAddress || !window.ethereum) {
            console.log('No cached wallet or MetaMask not detected');
            localStorage.removeItem('walletAddress');
            updateWalletButton();
            return;
        }

        web3 = new Web3(window.ethereum);
        try {
            const chainId = await window.ethereum.request({ method: 'eth_chainId' });
            if (chainId !== '0x38') {
                console.log('Incorrect chainId:', chainId);
                localStorage.removeItem('walletAddress');
                accounts = null;
                updateWalletButton();
                connectWalletFirstPopup.classList.remove('hidden');
                return;
            }

            accounts = await window.ethereum.request({ method: 'eth_accounts' });
            if (accounts && accounts.includes(cachedAddress)) {
                accounts = [cachedAddress];
                console.log('Cached wallet validated:', accounts[0]);
                updateWalletButton();
                mainContent.classList.remove('hidden');
                welcomePopup.classList.add('hidden');
                contentWrapper.classList.remove('blur');
                loadAgents();
            } else {
                console.log('Cached wallet not valid');
                localStorage.removeItem('walletAddress');
                accounts = null;
                updateWalletButton();
            }
        } catch (error) {
            console.error('Error checking cached wallet:', error.message);
            localStorage.removeItem('walletAddress');
            accounts = null;
            updateWalletButton();
        }
    }

    // Connect wallet (main button and welcome popup)
    connectWalletBtn.addEventListener('click', async () => {
        console.log('Connect Wallet button clicked, accounts:', accounts);
        if (!accounts || accounts.length === 0) {
            await initWeb3();
        } else {
            disconnectWalletPopup.classList.remove('hidden');
        }
    });

    welcomeConnectWallet.addEventListener('click', async () => {
        console.log('Welcome Connect Wallet button clicked');
        await initWeb3();
    });

    // Disconnect wallet
    document.getElementById('disconnectWallet').addEventListener('click', () => {
        console.log('Disconnect Wallet clicked');
        accounts = null;
        localStorage.removeItem('walletAddress');
        disconnectWalletPopup.classList.add('hidden');
        updateWalletButton();
    });

    document.getElementById('cancelDisconnect').addEventListener('click', () => {
        console.log('Cancel Disconnect clicked');
        disconnectWalletPopup.classList.add('hidden');
    });

    // Close connect wallet first popup
    document.getElementById('closeConnectWallet').addEventListener('click', () => {
        console.log('Close Connect Wallet popup clicked');
        connectWalletFirstPopup.classList.add('hidden');
    });

    // Close max agents popup
    document.getElementById('closeMaxAgents').addEventListener('click', () => {
        console.log('Close Max Agents popup clicked');
        maxAgentsPopup.classList.add('hidden');
    });

    // Open create agent popup
    createAgentBtn.addEventListener('click', () => {
        console.log('Create Agent clicked, window.ethereum:', !!window.ethereum, 'accounts:', accounts);
        if (!window.ethereum || !accounts || accounts.length === 0) {
            console.log('No wallet connected, showing connectWalletFirstPopup');
            connectWalletFirstPopup.classList.remove('hidden');
            return;
        }
        console.log('Opening create agent popup');
        createAgentPopup.classList.remove('hidden');
    });

    document.getElementById('cancelCreate').addEventListener('click', () => {
        console.log('Cancel Create clicked');
        createAgentPopup.classList.add('hidden');
        createAgentForm.reset();
    });

    // Close success popup
    document.getElementById('closeSuccess').addEventListener('click', () => {
        console.log('Close Success popup clicked');
        successPopup.classList.add('hidden');
    });

    // Create agent
    createAgentForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        console.log('Create Agent form submitted, accounts:', accounts);
        if (!window.ethereum || !accounts || accounts.length === 0) {
            console.log('No wallet connected during form submit');
            connectWalletFirstPopup.classList.remove('hidden');
            return;
        }

        const formData = new FormData(createAgentForm);
        formData.append('wallet_address', accounts[0]);

        try {
            console.log('Sending create agent request to backend');
            const response = await fetch('backend/create_agent.php', {
                method: 'POST',
                body: formData,
            });
            const result = await response.json();
            if (result.success) {
                console.log('Agent created successfully');
                successPopup.classList.remove('hidden');
                createAgentPopup.classList.add('hidden');
                createAgentForm.reset();
                loadAgents();
            } else {
                console.error('Error creating agent:', result.message);
                if (result.message === 'Maximum 3 agents allowed per wallet') {
                    maxAgentsPopup.classList.remove('hidden');
                } else {
                    alert('Error creating agent: ' + result.message);
                }
            }
        } catch (error) {
            console.error('Error:', error.message);
            alert('Failed to create agent: ' + error.message);
        }
    });

    // Load agents from database
    async function loadAgents() {
        console.log('loadAgents called, accounts:', accounts);
        if (!window.ethereum || !accounts || accounts.length === 0) {
            console.log('No wallet connected, clearing agent list');
            agentList.innerHTML = '';
            noAgentsMessage.classList.add('hidden');
            updateStats();
            return;
        }

        try {
            console.log('Fetching agents for wallet:', accounts[0]);
            const response = await fetch('backend/create_agent.php?action=list&wallet_address=' + accounts[0]);
            const data = await response.json();
            console.log('Fetch response:', data);
            if (!data.success) {
                throw new Error(data.message || 'Failed to fetch agents');
            }
            const agents = data.agents;
            const points = data.points || 0;
            const remainingAgents = data.remaining_agents !== undefined ? data.remaining_agents : 3;
            agentList.innerHTML = '';
            if (agents.length === 0) {
                console.log('No agents found, showing noAgentsMessage');
                noAgentsMessage.classList.remove('hidden');
                // Force recheck image visibility after DOM update
                setTimeout(() => {
                    if (!noAgentsMessage.classList.contains('hidden')) {
                        console.log('Rechecking noAgentsMessage visibility, hidden:', noAgentsMessage.classList.contains('hidden'));
                        const img = noAgentsMessage.querySelector('img');
                        if (img.src.includes('41bc996c50d0ac71b6c0bafe47f59ac3-1536x808.jpg') && !img.complete) {
                            console.log('Primary image not loaded, forcing fallback');
                            img.src = 'assets/no-agents.png';
                        }
                    }
                }, 1000);
                updateStats(0, points, remainingAgents);
            } else {
                console.log('Agents found:', agents.length, 'hiding noAgentsMessage');
                noAgentsMessage.classList.add('hidden');
                updateStats(agents.length, points, remainingAgents);
                agents.forEach(agent => {
                    const agentCard = document.createElement('div');
                    agentCard.className = 'agent-card';
                    agentCard.innerHTML = `
                        <img src="${agent.image}" alt="${agent.name}" class="w-full h-48 object-cover mb-4">
                        <h3 class="text-xl font-bold">${agent.name}</h3>
                        <p>Character: ${agent.character}</p>
                        <p>Skill: ${agent.skill}</p>
                        <button class="edit-agent btn-primary text-white py-2 px-4 rounded mt-2" data-id="${agent.id}">Edit</button>
                        <button class="try-ai btn-primary text-white py-2 px-4 rounded mt-2">Try AI</button>
                        <button class="connect-agi btn-primary text-white py-2 px-4 rounded mt-2">Connect to AGI</button>
                    `;
                    agentList.appendChild(agentCard);
                });

                document.querySelectorAll('.edit-agent').forEach(btn => {
                    btn.addEventListener('click', () => {
                        console.log('Edit Agent button clicked, id:', btn.dataset.id);
                        const agentId = btn.dataset.id;
                        const agent = agents.find(a => a.id == agentId);
                        editAgentForm.agent_id.value = agent.id;
                        editAgentForm.name.value = agent.name;
                        editAgentForm.character.value = agent.character;
                        editAgentForm.skill.value = agent.skill;
                        editAgentPopup.classList.remove('hidden');
                    });
                });
            }
        } catch (error) {
            console.error('Error loading agents:', error.message);
            noAgentsMessage.classList.remove('hidden');
            // Force recheck image visibility on error
            setTimeout(() => {
                console.log('Rechecking noAgentsMessage visibility on error, hidden:', noAgentsMessage.classList.contains('hidden'));
                const img = noAgentsMessage.querySelector('img');
                if (img.src.includes('41bc996c50d0ac71b6c0bafe47f59ac3-1536x808.jpg') && !img.complete) {
                    console.log('Primary image not loaded on error, forcing fallback');
                    img.src = 'assets/no-agents.png';
                }
            }, 1000);
            updateStats(0, 0, 3);
        }
    }

    // Edit agent
    editAgentForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        console.log('Edit Agent form submitted, accounts:', accounts);
        if (!window.ethereum || !accounts || accounts.length === 0) {
            console.log('No wallet connected during edit submit');
            connectWalletFirstPopup.classList.remove('hidden');
            return;
        }

        const formData = new FormData(editAgentForm);
        formData.append('wallet_address', accounts[0]);
        formData.append('action', 'edit');

        try {
            console.log('Sending edit agent request to backend');
            const response = await fetch('backend/create_agent.php', {
                method: 'POST',
                body: formData,
            });
            const result = await response.json();
            if (result.success) {
                console.log('Agent updated successfully');
                alert('Agent updated successfully!');
                editAgentPopup.classList.add('hidden');
                editAgentForm.reset();
                loadAgents();
            } else {
                console.error('Error updating agent:', result.message);
                alert('Error updating agent: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error.message);
            alert('Failed to update agent: ' + error.message);
        }
    });

    document.getElementById('cancelEdit').addEventListener('click', () => {
        console.log('Cancel Edit clicked');
        editAgentPopup.classList.add('hidden');
        editAgentForm.reset();
    });

    // Handle MetaMask account or chain changes
    window.ethereum?.on('accountsChanged', (newAccounts) => {
        console.log('Accounts changed:', newAccounts);
        accounts = newAccounts;
        if (accounts.length > 0) {
            localStorage.setItem('walletAddress', accounts[0]);
            updateWalletButton();
            loadAgents();
        } else {
            localStorage.removeItem('walletAddress');
            updateWalletButton();
            noAgentsMessage.classList.add('hidden');
            updateStats();
        }
    });

    window.ethereum?.on('chainChanged', (chainId) => {
        console.log('Chain changed:', chainId);
        if (chainId !== '0x38') {
            console.log('Chain changed to non-BSC, showing connectWalletFirstPopup');
            localStorage.removeItem('walletAddress');
            accounts = null;
            updateWalletButton();
            connectWalletFirstPopup.classList.remove('hidden');
            updateStats();
        } else {
            checkCachedWallet();
        }
    });

    // Initial load
    console.log('Initial load: checkCachedWallet');
    checkCachedWallet();

    // Test no-agents image
    const noAgentsImage = noAgentsMessage.querySelector('img');
    noAgentsImage.addEventListener('load', () => {
        console.log('No-agents image loaded successfully:', noAgentsImage.src);
    });
    noAgentsImage.addEventListener('error', () => {
        console.error('No-agents image failed to load:', noAgentsImage.src);
        noAgentsImage.src = 'assets/no-agents.png'; // Fallback
        console.log('Switched to fallback image: assets/no-agents.png');
        // Force DOM update
        noAgentsMessage.classList.add('hidden');
        noAgentsMessage.offsetHeight; // Trigger reflow
        noAgentsMessage.classList.remove('hidden');
    });
});