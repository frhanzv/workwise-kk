<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Chat - Workwise</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .vertical-writing {
            writing-mode: vertical-rl;
            text-orientation: mixed;
        }
    </style>
</head>
<body class="bg-gray-900 text-gray-100">
    
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar with Conversations -->
        <div id="chat-sidebar" class="w-64 bg-gray-800 border-r border-gray-700 flex flex-col transition-all duration-300 ease-in-out" style="margin-left: 0;">
            <!-- Sidebar Header -->
            <div class="p-4 border-b border-gray-700 flex items-center justify-between gap-2">
                <button 
                    onclick="createNewChat()"
                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg font-medium transition-colors flex items-center justify-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span class="text-sm">New Chat</span>
                </button>
                
                <!-- Hide Sidebar Button -->
                <button 
                    onclick="toggleSidebar()"
                    class="p-2 hover:bg-gray-700 rounded-lg transition-colors flex-shrink-0"
                    title="Hide conversations">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                    </svg>
                </button>
            </div>

            <!-- Conversations List -->
            <div id="conversationsList" class="flex-1 overflow-y-auto p-2">
                <div class="text-center text-gray-500 py-8 text-sm">
                    <svg class="w-8 h-8 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    <p>No conversations yet</p>
                </div>
            </div>
        </div>

        <!-- Show Sidebar Button (floating on left edge when hidden) -->
        <button 
            id="open-sidebar-btn"
            onclick="toggleSidebar()"
            class="hidden fixed left-0 top-1/2 -translate-y-1/2 z-50 bg-blue-600 hover:bg-blue-700 text-white px-2 py-6 rounded-r-lg shadow-lg transition-all flex flex-col items-center gap-2"
            title="Show conversations">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path>
            </svg>
            <span class="text-xs font-medium vertical-writing">Chat History</span>
        </button>

        <!-- Main Chat Area -->
        <div class="flex flex-col flex-1 transition-all duration-300">
            <!-- Header -->
            <div class="bg-gray-800 border-b border-gray-700 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div>
                            <h1 class="text-xl font-bold text-white">Analytics Assistant</h1>
                            <p class="text-sm text-gray-400">Ask questions about your data - I'll figure out what you need!</p>
                        </div>
                    </div>
                    
                    <!-- Server Status -->
                    <div id="serverStatusContainer" class="flex items-center gap-2 bg-gray-700/50 px-3 py-2 rounded-lg">
                        <div id="aiStatus" class="w-2 h-2 bg-yellow-500 rounded-full animate-pulse"></div>
                        <span id="aiStatusText" class="text-xs text-gray-400">Checking...</span>
                    </div>
                </div>
            </div>

            <!-- Chat Messages Container -->
            <div id="chatMessages" class="flex-1 overflow-y-auto px-6 py-4 space-y-4">
                <!-- Welcome Message -->
                <div class="flex gap-3">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <div class="bg-gray-800 rounded-lg p-4 border border-gray-700">
                            <p class="text-gray-300 font-medium mb-2">Hello! I'm your Analytics Assistant 🤖</p>
                            <p class="text-gray-400 text-sm mb-3">Just ask me anything about your data and I'll automatically find the right information! Examples:</p>
                            <ul class="space-y-2 text-sm text-gray-400">
                                <li class="flex items-start gap-2">
                                    <span class="text-blue-400">•</span>
                                    <span>"Which zone has the most worker check-ins?"</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-blue-400">•</span>
                                    <span>"Who is absent today?"</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-blue-400">•</span>
                                    <span>"List all workers in the management department"</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-blue-400">•</span>
                                    <span>"Show me worker details with IC numbers"</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Input Area -->
            <div class="bg-gray-800 border-t border-gray-700 px-6 py-4">
                <div class="max-w-4xl mx-auto">
                    <div class="flex gap-3">
                        <input 
                            type="text" 
                            id="messageInput" 
                            placeholder="Ask anything about your data..."
                            class="flex-1 bg-gray-700 text-white px-4 py-3 rounded-lg border border-gray-600 focus:outline-none focus:border-blue-500"
                            onkeypress="if(event.key === 'Enter') sendMessage()"
                        >
                        <button 
                            onclick="sendMessage()"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors flex items-center gap-2"
                        >
                            <span>Send</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        <span class="inline-flex items-center gap-1">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            Smart AI will automatically detect what data you need
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Context Menu for Conversations -->
    <div id="contextMenu" class="hidden fixed bg-gray-800 border border-gray-700 rounded-lg shadow-xl py-1 z-50" style="min-width: 160px;">
        <button onclick="renameConversation()" class="w-full px-4 py-2 text-left text-sm text-gray-300 hover:bg-gray-700 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            Rename
        </button>
        <button onclick="deleteConversation()" class="w-full px-4 py-2 text-left text-sm text-red-400 hover:bg-gray-700 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
            Delete
        </button>
    </div>

    <script>
        const chatMessages = document.getElementById('chatMessages');
        const messageInput = document.getElementById('messageInput');
        const conversationsList = document.getElementById('conversationsList');
        const contextMenu = document.getElementById('contextMenu');

        let currentConversationId = null;
        let conversations = [];
        let contextMenuTarget = null;
        let isSidebarOpen = true;

        // ============================================================
        // SIDEBAR TOGGLE FUNCTIONALITY
        // ============================================================
        
        function toggleSidebar() {
            const sidebar = document.getElementById('chat-sidebar');
            const openBtn = document.getElementById('open-sidebar-btn');
            
            if (isSidebarOpen) {
                // Hide sidebar - slide to the left
                sidebar.style.marginLeft = '-256px'; // -w-64 = -256px
                openBtn.classList.remove('hidden');
                isSidebarOpen = false;
            } else {
                // Show sidebar - slide back in
                sidebar.style.marginLeft = '0';
                openBtn.classList.add('hidden');
                isSidebarOpen = true;
            }
        }

        // ============================================================
        // SERVER STATUS MONITORING
        // ============================================================
        
        async function checkServerStatus() {
            try {
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 3000);
                
                const response = await fetch('http://localhost:8001/api/admin/status', {
                    method: 'GET',
                    signal: controller.signal
                });
                
                clearTimeout(timeoutId);
                
                if (response.ok) {
                    const data = await response.json();
                    document.getElementById('aiStatus').className = 'w-2 h-2 bg-green-500 rounded-full animate-pulse';
                    document.getElementById('aiStatusText').textContent = 'AI Active';
                    document.getElementById('aiStatusText').title = `Model: ${data.model || 'Unknown'}`;
                } else {
                    throw new Error('Server returned error');
                }
            } catch (error) {
                document.getElementById('aiStatus').className = 'w-2 h-2 bg-red-500 rounded-full';
                document.getElementById('aiStatusText').textContent = 'AI Offline';
                document.getElementById('aiStatusText').title = 'Python backend not running on localhost:8001';
            }
        }

        checkServerStatus();
        setInterval(checkServerStatus, 10000);

        // ============================================================
        // REST OF YOUR EXISTING CHAT FUNCTIONS
        // ============================================================

        loadConversations();

        function createNewChat() {
            currentConversationId = null;
            clearChatMessages();
            addWelcomeMessage();
            messageInput.focus();
            
            document.querySelectorAll('.conversation-item').forEach(item => {
                item.classList.remove('bg-gray-700', 'border-blue-500');
                item.classList.add('border-transparent');
            });
        }

        async function loadConversations() {
            // Your existing loadConversations code here
            try {
                const response = await fetch('<?= base_url("analytics/getConversations") ?>', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                });

                const data = await response.json();

                if (data.success && data.conversations.length > 0) {
                    conversations = data.conversations;
                    displayConversations(conversations);
                } else {
                    conversationsList.innerHTML = `
                        <div class="text-center text-gray-500 py-8 text-sm">
                            <svg class="w-8 h-8 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            <p>No conversations yet</p>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Failed to load conversations:', error);
            }
        }

        function displayConversations(convs) {
            conversationsList.innerHTML = '';

            convs.forEach(conv => {
                const convDiv = document.createElement('div');
                convDiv.className = `conversation-item relative group px-3 py-2 rounded-lg cursor-pointer transition-colors border border-transparent hover:bg-gray-700 ${
                    currentConversationId === conv.id ? 'bg-gray-700 border-blue-500' : ''
                }`;
                convDiv.dataset.conversationId = conv.id;

                const date = new Date(conv.updated_at);
                const timeStr = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });

                convDiv.innerHTML = `
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0 conversation-clickable">
                            <p class="text-sm text-white font-medium truncate mb-1">${escapeHtml(conv.title)}</p>
                            <p class="text-xs text-gray-400">${timeStr}</p>
                        </div>
                        <button 
                            class="menu-button ml-2 opacity-0 group-hover:opacity-100 transition-opacity text-gray-400 hover:text-white p-1"
                            data-conversation-id="${conv.id}"
                        >
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                            </svg>
                        </button>
                    </div>
                `;

                const clickableArea = convDiv.querySelector('.conversation-clickable');
                clickableArea.onclick = () => loadConversation(conv.id);

                const menuButton = convDiv.querySelector('.menu-button');
                menuButton.onclick = (e) => {
                    e.stopPropagation();
                    showContextMenu(e, conv.id);
                };

                conversationsList.appendChild(convDiv);
            });
        }

        async function loadConversation(conversationId) {
            try {
                const response = await fetch(`<?= base_url("analytics/getConversationMessages/") ?>${conversationId}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                });

                const data = await response.json();

                if (data.success) {
                    currentConversationId = conversationId;
                    
                    clearChatMessages();
                    addWelcomeMessage();
                    
                    data.messages.forEach(msg => {
                        addMessage(msg.question, true);
                        addMessage(msg.response, false);
                    });

                    document.querySelectorAll('.conversation-item').forEach(item => {
                        if (item.dataset.conversationId == conversationId) {
                            item.classList.add('bg-gray-700', 'border-blue-500');
                            item.classList.remove('border-transparent');
                        } else {
                            item.classList.remove('bg-gray-700', 'border-blue-500');
                            item.classList.add('border-transparent');
                        }
                    });
                } else {
                    alert('Failed to load conversation');
                }
            } catch (error) {
                console.error('Failed to load conversation:', error);
                alert('Failed to load conversation');
            }
        }

        function showContextMenu(event, conversationId) {
            event.stopPropagation();
            contextMenuTarget = conversationId;

            const menu = document.getElementById('contextMenu');
            menu.style.left = event.pageX + 'px';
            menu.style.top = event.pageY + 'px';
            menu.classList.remove('hidden');

            setTimeout(() => {
                document.addEventListener('click', closeContextMenu);
            }, 0);
        }

        function closeContextMenu() {
            const menu = document.getElementById('contextMenu');
            menu.classList.add('hidden');
            document.removeEventListener('click', closeContextMenu);
        }

        async function renameConversation() {
            closeContextMenu();
            const conversation = conversations.find(c => c.id === contextMenuTarget);
            if (!conversation) return;

            const newTitle = prompt('Enter new title:', conversation.title);
            if (!newTitle || newTitle === conversation.title) return;

            try {
                const response = await fetch('<?= base_url("analytics/renameConversation") ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        conversation_id: contextMenuTarget,
                        title: newTitle
                    })
                });

                const data = await response.json();

                if (data.success) {
                    loadConversations();
                } else {
                    alert('Failed to rename conversation: ' + (data.error || 'Unknown error'));
                }
            } catch (error) {
                console.error('Failed to rename conversation:', error);
                alert('Failed to rename conversation');
            }
        }

        async function deleteConversation() {
            closeContextMenu();
            if (!confirm('Are you sure you want to delete this conversation?')) return;

            try {
                const response = await fetch(`<?= base_url("analytics/deleteConversation/") ?>${contextMenuTarget}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                });

                const data = await response.json();

                if (data.success) {
                    if (currentConversationId === contextMenuTarget) {
                        createNewChat();
                    }
                    loadConversations();
                } else {
                    alert('Failed to delete conversation: ' + (data.error || 'Unknown error'));
                }
            } catch (error) {
                console.error('Failed to delete conversation:', error);
                alert('Failed to delete conversation');
            }
        }

        // Fixed createNewChat function - prevents sidebar from closing
function createNewChat() {
    // Prevent event bubbling that might trigger sidebar toggle
    event?.stopPropagation();
    
    currentConversationId = null;
    clearChatMessages();
    addWelcomeMessage();
    messageInput.focus();
    
    // Remove active state from all conversation items
    document.querySelectorAll('.conversation-item').forEach(item => {
        item.classList.remove('bg-gray-700', 'border-blue-500');
        item.classList.add('border-transparent');
    });
    
    // Ensure sidebar stays visible (don't change its state)
    // Remove any code that might be hiding the sidebar
}

// Also update the toggleSidebar function to be more explicit
function toggleSidebar() {
    const sidebar = document.getElementById('chat-sidebar');
    const openBtn = document.getElementById('open-sidebar-btn');
    
    if (isSidebarOpen) {
        // Hide sidebar - slide to the left
        sidebar.style.marginLeft = '-256px'; // -w-64 = -256px
        openBtn.classList.remove('hidden');
        isSidebarOpen = false;
    } else {
        // Show sidebar - slide back in
        sidebar.style.marginLeft = '0';
        openBtn.classList.add('hidden');
        isSidebarOpen = true;
    }
}

function addWelcomeMessage() {
    const welcomeDiv = document.createElement('div');
    welcomeDiv.className = 'flex gap-3';
    welcomeDiv.innerHTML = `
        <div class="flex-shrink-0">
            <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </div>
        </div>
        <div class="flex-1">
            <div class="bg-gray-800 rounded-lg p-4 border border-gray-700">
                <p class="text-gray-300 font-medium mb-2">Hello! I'm your Analytics Assistant 🤖</p>
                <p class="text-gray-400 text-sm mb-3">Just ask me anything about your data and I'll automatically find the right information! Examples:</p>
                <ul class="space-y-2 text-sm text-gray-400">
                    <li class="flex items-start gap-2">
                        <span class="text-blue-400">•</span>
                        <span>"Which zone has the most worker check-ins?"</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-blue-400">•</span>
                        <span>"Who is absent today?"</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-blue-400">•</span>
                        <span>"List all workers in the management department"</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-blue-400">•</span>
                        <span>"Show me worker details with IC numbers"</span>
                    </li>
                </ul>
            </div>
        </div>
    `;
    
    chatMessages.appendChild(welcomeDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

        function addMessage(content, isUser = false) {
            const messageDiv = document.createElement('div');
            messageDiv.className = 'flex gap-3';
            
            const icon = isUser 
                ? `<div class="flex-shrink-0">
                     <div class="w-8 h-8 rounded-full bg-gray-600 flex items-center justify-center">
                       <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                       </svg>
                     </div>
                   </div>`
                : `<div class="flex-shrink-0">
                     <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center">
                       <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                       </svg>
                     </div>
                   </div>`;
            
            messageDiv.innerHTML = `
                ${icon}
                <div class="flex-1">
                    <div class="bg-${isUser ? 'gray-700' : 'gray-800'} rounded-lg p-4 border border-gray-${isUser ? '600' : '700'}">
                        <p class="text-gray-300 whitespace-pre-wrap">${escapeHtml(content)}</p>
                    </div>
                </div>
            `;
            
            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function clearChatMessages() {
            chatMessages.innerHTML = '';
        }

        async function sendMessage() {
            const message = messageInput.value.trim();
            if (!message) return;

            addMessage(message, true);
            messageInput.value = '';
            addLoadingMessage();

            try {
                const response = await fetch('<?= base_url('analytics/query') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        message: message,
                        conversation_id: currentConversationId
                    })
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();
                removeLoadingMessage();

                if (data.error) {
                    addMessage(`Error: ${data.error}\n${data.message || ''}`, false);
                } else {
                    addMessage(data.response || data.message || 'No response received', false);
                    
                    if (data.conversation_id && !currentConversationId) {
                        currentConversationId = data.conversation_id;
                        loadConversations();
                    }
                }
            } catch (error) {
                removeLoadingMessage();
                addMessage(`Connection error: ${error.message}\n\nMake sure the Python analytics backend is running on http://localhost:8001`, false);
            }
        }

        function addLoadingMessage() {
            const loadingDiv = document.createElement('div');
            loadingDiv.id = 'loadingMessage';
            loadingDiv.className = 'flex gap-3';
            loadingDiv.innerHTML = `
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center">
                        <svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <div class="bg-gray-800 rounded-lg p-4 border border-gray-700">
                        <p class="text-gray-400">Analyzing data...</p>
                    </div>
                </div>
            `;
            chatMessages.appendChild(loadingDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function removeLoadingMessage() {
            const loading = document.getElementById('loadingMessage');
            if (loading) loading.remove();
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        messageInput.focus();
    </script>
</body>
</html>