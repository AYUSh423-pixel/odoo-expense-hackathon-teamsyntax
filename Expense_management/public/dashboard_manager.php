<?php
require_once '../includes/auth.php';
// requireLogin(); // Temporarily disabled for debugging
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard - Expense Manager</title>
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
            <h2 class="text-3xl font-bold text-gray-900">Manager Dashboard</h2>
            <p class="text-gray-600 mt-2">Review and approve expense claims</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-clock text-yellow-500 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Pending Approvals</p>
                        <p class="text-2xl font-semibold text-gray-900" id="pendingCount">-</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-500 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Approved Today</p>
                        <p class="text-2xl font-semibold text-gray-900" id="approvedToday">-</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-times-circle text-red-500 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Rejected Today</p>
                        <p class="text-2xl font-semibold text-gray-900" id="rejectedToday">-</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mb-8 flex flex-wrap gap-4">
            <button onclick="loadPendingApprovals()" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition duration-200 flex items-center">
                <i class="fas fa-refresh mr-2"></i>Refresh Approvals
            </button>
            <button onclick="loadAllExpenses()" class="bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition duration-200 flex items-center">
                <i class="fas fa-list mr-2"></i>View All Expenses
            </button>
        </div>

        <!-- Pending Approvals -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Pending Approvals</h3>
            </div>
            <div id="pendingApprovalsList" class="divide-y divide-gray-200">
                <div class="p-6 text-center text-gray-500">
                    <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                    <p>Loading pending approvals...</p>
                </div>
            </div>
        </div>

        <!-- All Expenses (Hidden by default) -->
        <div id="allExpensesSection" class="hidden mt-8 bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">All Expenses</h3>
            </div>
            <div id="allExpensesList" class="divide-y divide-gray-200">
                <div class="p-6 text-center text-gray-500">
                    <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                    <p>Loading expenses...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Approval Modal -->
    <div id="approvalModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Approve Expense</h3>
                    <button onclick="closeApprovalModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div id="modalExpenseDetails" class="mb-4 p-4 bg-gray-50 rounded-lg">
                    <!-- Expense details will be populated here -->
                </div>
                
                <div class="mb-4">
                    <label for="approvalComments" class="block text-sm font-medium text-gray-700 mb-2">Comments (Optional)</label>
                    <textarea id="approvalComments" rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              placeholder="Add any comments..."></textarea>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button onclick="closeApprovalModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">
                        Cancel
                    </button>
                    <button onclick="rejectExpense()" id="rejectBtn" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200">
                        <i class="fas fa-times mr-2"></i>Reject
                    </button>
                    <button onclick="approveExpense()" id="approveBtn" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200">
                        <i class="fas fa-check mr-2"></i>Approve
                    </button>
                </div>
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
        let currentExpenseId = null;

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

        // Load pending approvals
        async function loadPendingApprovals() {
            try {
                const response = await fetch('/Expense_management/api/approvals.php?action=pending');
                const data = await response.json();
                
                if (data.success) {
                    displayPendingApprovals(data.approvals);
                    updateStats(data.approvals);
                } else {
                    throw new Error(data.error || 'Failed to load pending approvals');
                }
            } catch (error) {
                showError('Failed to load pending approvals: ' + error.message);
            }
        }

        // Display pending approvals
        function displayPendingApprovals(approvals) {
            const container = document.getElementById('pendingApprovalsList');
            
            if (approvals.length === 0) {
                container.innerHTML = `
                    <div class="p-6 text-center text-gray-500">
                        <i class="fas fa-check-circle text-4xl mb-4"></i>
                        <p class="text-lg">No pending approvals</p>
                        <p class="text-sm">All caught up! 🎉</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = approvals.map(approval => `
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                        Step ${approval.step_order} - Pending
                                    </span>
                                </div>
                                <div>
                                    <h4 class="text-lg font-medium text-gray-900">${approval.category}</h4>
                                    <p class="text-sm text-gray-600">${approval.description || 'No description'}</p>
                                    <p class="text-xs text-gray-500">
                                        Submitted by ${approval.submitter_name} on ${new Date(approval.created_at).toLocaleDateString()}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-semibold text-gray-900">
                                ${approval.original_currency} ${parseFloat(approval.original_amount).toFixed(2)}
                            </div>
                            ${approval.company_currency !== approval.original_currency ? 
                                `<div class="text-sm text-gray-600">
                                    ≈ ${approval.company_currency} ${parseFloat(approval.company_amount).toFixed(2)}
                                </div>` : ''
                            }
                            <div class="text-xs text-gray-500">
                                ${approval.approved_count}/${approval.total_approvals} approvals
                            </div>
                        </div>
                        <div class="ml-4 flex space-x-2">
                            <button onclick="openApprovalModal(${approval.id}, 'approve')" 
                                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200 text-sm">
                                <i class="fas fa-check mr-1"></i>Approve
                            </button>
                            <button onclick="openApprovalModal(${approval.id}, 'reject')" 
                                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200 text-sm">
                                <i class="fas fa-times mr-1"></i>Reject
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        // Load all expenses
        async function loadAllExpenses() {
            try {
                const response = await fetch('/Expense_management/api/expenses.php?action=list');
                const data = await response.json();
                
                if (data.success) {
                    displayAllExpenses(data.expenses);
                    document.getElementById('allExpensesSection').classList.remove('hidden');
                } else {
                    throw new Error(data.error || 'Failed to load expenses');
                }
            } catch (error) {
                showError('Failed to load expenses: ' + error.message);
            }
        }

        // Display all expenses
        function displayAllExpenses(expenses) {
            const container = document.getElementById('allExpensesList');
            
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
                    </div>
                </div>
            `).join('');
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

        // Update stats
        function updateStats(approvals) {
            document.getElementById('pendingCount').textContent = approvals.length;
            
            // Calculate today's stats (simplified)
            const today = new Date().toDateString();
            const todayApprovals = approvals.filter(a => new Date(a.created_at).toDateString() === today);
            
            document.getElementById('approvedToday').textContent = '0'; // Would need separate API call
            document.getElementById('rejectedToday').textContent = '0'; // Would need separate API call
        }

        // Open approval modal
        function openApprovalModal(expenseId, action) {
            currentExpenseId = expenseId;
            
            // Find the expense details
            const expenseElement = document.querySelector(`[onclick*="${expenseId}"]`).closest('.p-6');
            const category = expenseElement.querySelector('h4').textContent;
            const description = expenseElement.querySelector('p.text-sm').textContent;
            const amount = expenseElement.querySelector('.text-lg.font-semibold').textContent;
            
            document.getElementById('modalTitle').textContent = action === 'approve' ? 'Approve Expense' : 'Reject Expense';
            document.getElementById('modalExpenseDetails').innerHTML = `
                <div class="space-y-2">
                    <div><strong>Category:</strong> ${category}</div>
                    <div><strong>Description:</strong> ${description}</div>
                    <div><strong>Amount:</strong> ${amount}</div>
                </div>
            `;
            
            document.getElementById('approveBtn').style.display = action === 'approve' ? 'block' : 'none';
            document.getElementById('rejectBtn').style.display = action === 'reject' ? 'block' : 'none';
            
            document.getElementById('approvalModal').classList.remove('hidden');
        }

        // Close approval modal
        function closeApprovalModal() {
            document.getElementById('approvalModal').classList.add('hidden');
            document.getElementById('approvalComments').value = '';
            currentExpenseId = null;
        }

        // Approve expense
        async function approveExpense() {
            if (!currentExpenseId) return;
            
            const comments = document.getElementById('approvalComments').value;
            
            try {
                const response = await fetch('/Expense_management/api/approvals.php?action=approve', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        expense_id: currentExpenseId,
                        comments: comments
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showSuccess('Expense approved successfully!');
                    closeApprovalModal();
                    loadPendingApprovals();
                } else {
                    throw new Error(data.error || 'Failed to approve expense');
                }
            } catch (error) {
                showError('Failed to approve expense: ' + error.message);
            }
        }

        // Reject expense
        async function rejectExpense() {
            if (!currentExpenseId) return;
            
            const comments = document.getElementById('approvalComments').value;
            
            if (!comments.trim()) {
                showError('Comments are required for rejection');
                return;
            }
            
            try {
                const response = await fetch('/Expense_management/api/approvals.php?action=reject', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        expense_id: currentExpenseId,
                        comments: comments
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showSuccess('Expense rejected successfully!');
                    closeApprovalModal();
                    loadPendingApprovals();
                } else {
                    throw new Error(data.error || 'Failed to reject expense');
                }
            } catch (error) {
                showError('Failed to reject expense: ' + error.message);
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

        // Logout
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = '/Expense_management/api/auth.php?action=logout';
            }
        }

        // Initialize page
        loadUserData();
        loadPendingApprovals();
    </script>
</body>
</html>
