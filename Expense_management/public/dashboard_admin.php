<?php
require_once '../includes/auth.php';
// requireLogin(); // Temporarily disabled for debugging
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Expense Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <i class="fas fa-receipt text-blue-600 text-2xl mr-3"></i>
                        <h1 class="text-xl font-bold text-gray-900">Expense Manager</h1>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-700" id="userName">Loading...</span>
                    <button onclick="logout()" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition duration-200">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-gray-900">Admin Dashboard</h2>
            <p class="text-gray-600 mt-2">Manage users, expenses, and company settings</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-users text-blue-500 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Users</p>
                        <p class="text-2xl font-semibold text-gray-900" id="totalUsers">-</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-receipt text-green-500 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Expenses</p>
                        <p class="text-2xl font-semibold text-gray-900" id="totalExpenses">-</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-clock text-yellow-500 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Pending Approvals</p>
                        <p class="text-2xl font-semibold text-gray-900" id="pendingApprovals">-</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-dollar-sign text-purple-500 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Amount</p>
                        <p class="text-2xl font-semibold text-gray-900" id="totalAmount">-</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mb-8 flex flex-wrap gap-4">
            <button onclick="showUserManagement()" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition duration-200 flex items-center">
                <i class="fas fa-users mr-2"></i>Manage Users
            </button>
            <button onclick="showExpenseManagement()" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition duration-200 flex items-center">
                <i class="fas fa-receipt mr-2"></i>Manage Expenses
            </button>
            <button onclick="showWorkflowSettings()" class="bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 transition duration-200 flex items-center">
                <i class="fas fa-cogs mr-2"></i>Workflow Settings
            </button>
            <button onclick="setupDefaultApprovals()" class="bg-orange-600 text-white px-6 py-3 rounded-lg hover:bg-orange-700 transition duration-200 flex items-center">
                <i class="fas fa-magic mr-2"></i>Setup Default Approvals
            </button>
            <button onclick="loadDashboard()" class="bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition duration-200 flex items-center">
                <i class="fas fa-refresh mr-2"></i>Refresh
            </button>
        </div>

        <!-- Main Content Area -->
        <div id="mainContent">
            <!-- User Management Section -->
            <div id="userManagement" class="hidden">
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-900">User Management</h3>
                        <button onclick="showAddUserForm()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                            <i class="fas fa-plus mr-2"></i>Add User
                        </button>
                    </div>
                    <div id="usersList" class="divide-y divide-gray-200">
                        <div class="p-6 text-center text-gray-500">
                            <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                            <p>Loading users...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Expense Management Section -->
            <div id="expenseManagement" class="hidden">
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Expense Management</h3>
                    </div>
                    <div id="expensesList" class="divide-y divide-gray-200">
                        <div class="p-6 text-center text-gray-500">
                            <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                            <p>Loading expenses...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Workflow Settings Section -->
            <div id="workflowSettings" class="hidden">
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Workflow Settings</h3>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-600">Workflow configuration will be implemented here.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div id="addUserModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Add New User</h3>
                    <button onclick="closeAddUserModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form id="addUserForm" class="space-y-4">
                    <div>
                        <label for="userName" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <input type="text" id="userName" name="name" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label for="userEmail" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" id="userEmail" name="email" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label for="userPassword" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input type="password" id="userPassword" name="password" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label for="userRole" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                        <select id="userRole" name="role" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="Employee">Employee</option>
                            <option value="Manager">Manager</option>
                            <option value="Finance">Finance</option>
                            <option value="Director">Director</option>
                            <option value="HR">HR</option>
                            <option value="CFO">CFO</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="userManager" class="block text-sm font-medium text-gray-700 mb-1">Manager (Optional)</label>
                        <select id="userManager" name="manager_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Select Manager</option>
                        </select>
                    </div>
                    
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" onclick="closeAddUserModal()" 
                                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
                            Add User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Error Alert -->
    <div id="errorAlert" class="hidden fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg shadow-lg z-50">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <span id="errorMessage"></span>
        </div>
    </div>

    <!-- Success Alert -->
    <div id="successAlert" class="hidden fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg shadow-lg z-50">
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            <span id="successMessage"></span>
        </div>
    </div>

    <script>
        let currentUser = null;

        // Load user data from PHP session
        function loadUserData() {
            // Check if user is logged in via session
            const userLoggedIn = '<?php echo isset($_SESSION["user"]) ? "true" : "false"; ?>';
            
            if (userLoggedIn === 'true') {
                currentUser = {
                    id: '<?php echo $_SESSION["user"]["id"] ?? ""; ?>',
                    name: '<?php echo $_SESSION["user"]["name"] ?? ""; ?>',
                    email: '<?php echo $_SESSION["user"]["email"] ?? ""; ?>',
                    role: '<?php echo $_SESSION["user"]["role"] ?? ""; ?>',
                    company_id: '<?php echo $_SESSION["user"]["company_id"] ?? ""; ?>'
                };
                document.getElementById('userName').textContent = currentUser.name;
            } else {
                // Redirect to login if not logged in
                window.location.href = 'index.php';
                return;
            }
        }

        // Load dashboard data
        async function loadDashboard() {
            await Promise.all([
                loadUsers(),
                loadExpenses(),
                loadStats()
            ]);
        }

        // Load users
        async function loadUsers() {
            try {
                const response = await fetch('/Expense_management/api/users.php?action=list');
                const data = await response.json();
                
                if (data.success) {
                    displayUsers(data.users);
                    populateManagerSelect(data.users);
                } else {
                    throw new Error(data.error || 'Failed to load users');
                }
            } catch (error) {
                showError('Failed to load users: ' + error.message);
            }
        }

        // Display users
        function displayUsers(users) {
            const container = document.getElementById('usersList');
            
            if (users.length === 0) {
                container.innerHTML = `
                    <div class="p-6 text-center text-gray-500">
                        <i class="fas fa-users text-4xl mb-4"></i>
                        <p class="text-lg">No users found</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = users.map(user => `
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${getRoleColor(user.role)}">
                                        ${user.role}
                                    </span>
                                </div>
                                <div>
                                    <h4 class="text-lg font-medium text-gray-900">${user.name}</h4>
                                    <p class="text-sm text-gray-600">${user.email}</p>
                                    ${user.manager_name ? `<p class="text-xs text-gray-500">Manager: ${user.manager_name}</p>` : ''}
                                </div>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="editUser(${user.id})" class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700 transition duration-200">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </button>
                            <button onclick="deleteUser(${user.id})" class="px-3 py-1 bg-red-600 text-white rounded text-sm hover:bg-red-700 transition duration-200">
                                <i class="fas fa-trash mr-1"></i>Delete
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        // Load expenses
        async function loadExpenses() {
            try {
                const response = await fetch('/Expense_management/api/expenses.php?action=list');
                const data = await response.json();
                
                if (data.success) {
                    displayExpenses(data.expenses);
                } else {
                    throw new Error(data.error || 'Failed to load expenses');
                }
            } catch (error) {
                showError('Failed to load expenses: ' + error.message);
            }
        }

        // Display expenses
        function displayExpenses(expenses) {
            const container = document.getElementById('expensesList');
            
            if (expenses.length === 0) {
                container.innerHTML = `
                    <div class="p-6 text-center text-gray-500">
                        <i class="fas fa-receipt text-4xl mb-4"></i>
                        <p class="text-lg">No expenses found</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = expenses.map(expense => `
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${getStatusColor(expense.status)}">
                                        ${expense.status}
                                    </span>
                                </div>
                                <div>
                                    <h4 class="text-lg font-medium text-gray-900">${expense.category}</h4>
                                    <p class="text-sm text-gray-600">${expense.description || 'No description'}</p>
                                    <p class="text-xs text-gray-500">
                                        Submitted by ${expense.user_name} on ${new Date(expense.created_at).toLocaleDateString()}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-semibold text-gray-900">
                                ${expense.original_currency} ${parseFloat(expense.original_amount).toFixed(2)}
                            </div>
                            ${expense.company_currency !== expense.original_currency ? 
                                `<div class="text-sm text-gray-600">
                                    ≈ ${expense.company_currency} ${parseFloat(expense.company_amount).toFixed(2)}
                                </div>` : ''
                            }
                            <div class="text-xs text-gray-500">
                                ${expense.approved_count}/${expense.total_approvals} approvals
                            </div>
                        </div>
                        <div class="ml-4 flex space-x-2">
                            ${expense.status === 'Pending' ? `
                                <button onclick="overrideExpense(${expense.id}, 'Approved')" 
                                        class="px-3 py-1 bg-green-600 text-white rounded text-sm hover:bg-green-700 transition duration-200">
                                    <i class="fas fa-check mr-1"></i>Approve
                                </button>
                                <button onclick="overrideExpense(${expense.id}, 'Rejected')" 
                                        class="px-3 py-1 bg-red-600 text-white rounded text-sm hover:bg-red-700 transition duration-200">
                                    <i class="fas fa-times mr-1"></i>Reject
                                </button>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `).join('');
        }

        // Load stats
        async function loadStats() {
            try {
                const response = await fetch('/Expense_management/api/stats.php');
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('totalUsers').textContent = data.stats.total_users;
                    document.getElementById('totalExpenses').textContent = data.stats.total_expenses;
                    document.getElementById('pendingApprovals').textContent = data.stats.pending_approvals;
                    document.getElementById('totalAmount').textContent = data.company_currency + ' ' + parseFloat(data.stats.total_amount).toFixed(2);
                } else {
                    throw new Error(data.error || 'Failed to load stats');
                }
            } catch (error) {
                console.error('Failed to load stats:', error);
                // Keep default values
                document.getElementById('totalUsers').textContent = '0';
                document.getElementById('totalExpenses').textContent = '0';
                document.getElementById('pendingApprovals').textContent = '0';
                document.getElementById('totalAmount').textContent = '$0.00';
            }
        }

        // Get role color
        function getRoleColor(role) {
            switch (role) {
                case 'Admin': return 'bg-purple-100 text-purple-800';
                case 'Manager': return 'bg-blue-100 text-blue-800';
                case 'Finance': return 'bg-green-100 text-green-800';
                case 'Director': return 'bg-yellow-100 text-yellow-800';
                case 'HR': return 'bg-pink-100 text-pink-800';
                case 'CFO': return 'bg-indigo-100 text-indigo-800';
                case 'Employee': return 'bg-gray-100 text-gray-800';
                default: return 'bg-gray-100 text-gray-800';
            }
        }

        // Get status color
        function getStatusColor(status) {
            switch (status) {
                case 'Approved': return 'bg-green-100 text-green-800';
                case 'Rejected': return 'bg-red-100 text-red-800';
                case 'Pending': return 'bg-yellow-100 text-yellow-800';
                default: return 'bg-gray-100 text-gray-800';
            }
        }

        // Show sections
        function showUserManagement() {
            hideAllSections();
            document.getElementById('userManagement').classList.remove('hidden');
            loadUsers();
        }

        function showExpenseManagement() {
            hideAllSections();
            document.getElementById('expenseManagement').classList.remove('hidden');
            loadExpenses();
        }

        function showWorkflowSettings() {
            hideAllSections();
            document.getElementById('workflowSettings').classList.remove('hidden');
        }

        function hideAllSections() {
            document.getElementById('userManagement').classList.add('hidden');
            document.getElementById('expenseManagement').classList.add('hidden');
            document.getElementById('workflowSettings').classList.add('hidden');
        }

        // User management functions
        function showAddUserForm() {
            document.getElementById('addUserModal').classList.remove('hidden');
        }

        function closeAddUserModal() {
            document.getElementById('addUserModal').classList.add('hidden');
            document.getElementById('addUserForm').reset();
            
            // Reset form to create mode
            document.querySelector('#addUserModal h3').textContent = 'Add New User';
            const submitBtn = document.querySelector('#addUserModal button[type="submit"]');
            submitBtn.textContent = 'Add User';
            submitBtn.removeAttribute('data-user-id');
        }

        function populateManagerSelect(users) {
            const select = document.getElementById('userManager');
            select.innerHTML = '<option value="">Select Manager</option>';
            
            users.filter(user => ['Manager', 'Admin', 'HR', 'CFO'].includes(user.role)).forEach(user => {
                const option = document.createElement('option');
                option.value = user.id;
                option.textContent = user.name;
                select.appendChild(option);
            });
        }

        // Add user form submission
        document.getElementById('addUserForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            const submitBtn = this.querySelector('button[type="submit"]');
            const isEdit = submitBtn.hasAttribute('data-user-id');
            
            try {
                const action = isEdit ? 'update' : 'create';
                if (isEdit) {
                    data.user_id = submitBtn.getAttribute('data-user-id');
                }
                
                const response = await fetch(`/Expense_management/api/users.php?action=${action}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showSuccess(`User ${isEdit ? 'updated' : 'created'} successfully!`);
                    closeAddUserModal();
                    loadUsers();
                } else {
                    throw new Error(result.error || `Failed to ${isEdit ? 'update' : 'create'} user`);
                }
            } catch (error) {
                showError(`Failed to ${isEdit ? 'update' : 'create'} user: ` + error.message);
            }
        });

        // Edit user
        function editUser(userId) {
            // Find user data
            const userElement = document.querySelector(`[onclick*="editUser(${userId})"]`).closest('.p-6');
            const name = userElement.querySelector('h4').textContent;
            const email = userElement.querySelector('p.text-sm').textContent;
            const role = userElement.querySelector('.inline-flex').textContent.trim();
            
            // Pre-fill the add user form
            document.getElementById('userName').value = name;
            document.getElementById('userEmail').value = email;
            document.getElementById('userRole').value = role;
            
            // Show the modal
            document.getElementById('addUserModal').classList.remove('hidden');
            
            // Change form to edit mode
            document.querySelector('#addUserModal h3').textContent = 'Edit User';
            document.querySelector('#addUserModal button[type="submit"]').textContent = 'Update User';
            document.querySelector('#addUserModal button[type="submit"]').setAttribute('data-user-id', userId);
        }

        // Delete user
        async function deleteUser(userId) {
            if (!confirm('Are you sure you want to delete this user?')) {
                return;
            }
            
            try {
                const response = await fetch('/Expense_management/api/users.php?action=delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        user_id: userId
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showSuccess('User deleted successfully!');
                    loadUsers();
                } else {
                    throw new Error(data.error || 'Failed to delete user');
                }
            } catch (error) {
                showError('Failed to delete user: ' + error.message);
            }
        }

        // Override expense
        async function overrideExpense(expenseId, status) {
            const comments = prompt(`Enter comments for ${status.toLowerCase()}:`);
            if (comments === null) return;
            
            try {
                const response = await fetch('/Expense_management/api/approvals.php?action=override', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        expense_id: expenseId,
                        status: status,
                        comments: comments
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showSuccess(`Expense ${status.toLowerCase()} successfully!`);
                    loadExpenses();
                } else {
                    throw new Error(data.error || 'Failed to override expense');
                }
            } catch (error) {
                showError('Failed to override expense: ' + error.message);
            }
        }

        // Show error message
        function showError(message) {
            document.getElementById('errorMessage').textContent = message;
            document.getElementById('errorAlert').classList.remove('hidden');
            setTimeout(() => {
                document.getElementById('errorAlert').classList.add('hidden');
            }, 5000);
        }

        // Show success message
        function showSuccess(message) {
            document.getElementById('successMessage').textContent = message;
            document.getElementById('successAlert').classList.remove('hidden');
            setTimeout(() => {
                document.getElementById('successAlert').classList.add('hidden');
            }, 5000);
        }

        // Setup default approvals
        async function setupDefaultApprovals() {
            if (!confirm('This will setup a default approval workflow with Manager → HR → Finance → CFO → Director. Continue?')) {
                return;
            }
            
            try {
                const response = await fetch('/Expense_management/api/setup_approvals.php?action=setup_default', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showSuccess('Default approval workflow setup successfully! CFO and HR users will now see pending approvals.');
                } else {
                    throw new Error(data.error || 'Failed to setup default approvals');
                }
            } catch (error) {
                showError('Failed to setup default approvals: ' + error.message);
            }
        }

        // Logout
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = '/Expense_management/api/auth.php?action=logout';
            }
        }

        // Initialize page
        loadUserData();
        loadDashboard();
        showUserManagement(); // Show user management by default
    </script>
</body>
</html>
