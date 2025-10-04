<?php
require_once '../includes/auth.php';
// requireLogin(); // Temporarily disabled for debugging
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard - Expense Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tesseract.js@2.1.4/dist/tesseract.min.js"></script>
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
            <h2 class="text-3xl font-bold text-gray-900">Employee Dashboard</h2>
            <p class="text-gray-600 mt-2">Manage your expense claims and track approvals</p>
        </div>

        <!-- Action Buttons -->
        <div class="mb-8 flex flex-wrap gap-4">
            <button onclick="showSubmitForm()" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition duration-200 flex items-center">
                <i class="fas fa-plus mr-2"></i>Submit New Expense
            </button>
            <button onclick="loadExpenses()" class="bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition duration-200 flex items-center">
                <i class="fas fa-refresh mr-2"></i>Refresh
            </button>
        </div>

        <!-- Submit Expense Form (Hidden by default) -->
        <div id="submitForm" class="hidden mb-8 bg-white rounded-lg shadow p-6">
            <h3 class="text-xl font-semibold text-gray-900 mb-4">Submit New Expense</h3>
            <form id="expenseForm" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="original_amount" class="block text-sm font-medium text-gray-700 mb-2">Amount</label>
                        <input type="number" id="original_amount" name="original_amount" step="0.01" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label for="original_currency" class="block text-sm font-medium text-gray-700 mb-2">Currency</label>
                        <select id="original_currency" name="original_currency" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="USD">USD</option>
                            <option value="EUR">EUR</option>
                            <option value="GBP">GBP</option>
                            <option value="INR">INR</option>
                            <option value="JPY">JPY</option>
                            <option value="CAD">CAD</option>
                            <option value="AUD">AUD</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                        <select id="category" name="category" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Select Category</option>
                            <option value="Travel">Travel</option>
                            <option value="Meals">Meals</option>
                            <option value="Office Supplies">Office Supplies</option>
                            <option value="Transportation">Transportation</option>
                            <option value="Accommodation">Accommodation</option>
                            <option value="Entertainment">Entertainment</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label for="expense_date" class="block text-sm font-medium text-gray-700 mb-2">Expense Date</label>
                        <input type="date" id="expense_date" name="expense_date" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea id="description" name="description" rows="3"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              placeholder="Describe the expense..."></textarea>
                </div>

                <!-- OCR Receipt Upload -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Receipt (Optional - OCR will auto-fill fields)</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-gray-400 transition duration-200">
                        <div class="space-y-1 text-center">
                            <i class="fas fa-cloud-upload-alt text-gray-400 text-4xl"></i>
                            <div class="flex text-sm text-gray-600">
                                <label for="receipt" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                    <span>Upload receipt image</span>
                                    <input id="receipt" name="receipt" type="file" accept="image/*" class="sr-only" onchange="processReceipt()">
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
                        </div>
                    </div>
                </div>

                <!-- Currency Conversion Preview -->
                <div id="conversionPreview" class="hidden bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <i class="fas fa-exchange-alt text-blue-600 mr-2"></i>
                        <span class="text-sm text-blue-800">
                            <span id="conversionText"></span>
                        </span>
                    </div>
                </div>

                <div class="flex justify-end space-x-4">
                    <button type="button" onclick="hideSubmitForm()" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">
                        Cancel
                    </button>
                    <button type="submit" id="submitBtn" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
                        <span id="submitBtnText">Submit Expense</span>
                        <i id="submitBtnSpinner" class="fas fa-spinner fa-spin ml-2 hidden"></i>
                    </button>
                </div>
            </form>
        </div>

        <!-- Expenses List -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">My Expenses</h3>
            </div>
            <div id="expensesList" class="divide-y divide-gray-200">
                <div class="p-6 text-center text-gray-500">
                    <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                    <p>Loading expenses...</p>
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
                        <p class="text-sm">Submit your first expense to get started</p>
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
                                    <p class="text-xs text-gray-500">${new Date(expense.expense_date).toLocaleDateString()}</p>
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

        // Show submit form
        function showSubmitForm() {
            document.getElementById('submitForm').classList.remove('hidden');
            document.getElementById('expense_date').value = new Date().toISOString().split('T')[0];
        }

        // Hide submit form
        function hideSubmitForm() {
            document.getElementById('submitForm').classList.add('hidden');
            document.getElementById('expenseForm').reset();
            document.getElementById('conversionPreview').classList.add('hidden');
        }

        // Process receipt with OCR
        async function processReceipt() {
            const fileInput = document.getElementById('receipt');
            const file = fileInput.files[0];
            
            if (!file) return;
            
            try {
                const { createWorker } = Tesseract;
                const worker = createWorker();
                
                await worker.load();
                await worker.loadLanguage('eng');
                await worker.initialize('eng');
                
                const { data: { text } } = await worker.recognize(file);
                await worker.terminate();
                
                // Parse OCR text
                const parsed = parseOCRText(text);
                
                if (parsed.amount) {
                    document.getElementById('original_amount').value = parsed.amount;
                }
                if (parsed.date) {
                    // Try to parse and format date
                    const date = new Date(parsed.date);
                    if (!isNaN(date.getTime())) {
                        document.getElementById('expense_date').value = date.toISOString().split('T')[0];
                    }
                }
                if (parsed.merchant) {
                    document.getElementById('description').value = parsed.merchant;
                }
                
                showSuccess('Receipt processed successfully! Please review the auto-filled fields.');
                
            } catch (error) {
                showError('Failed to process receipt: ' + error.message);
            }
        }

        // Parse OCR text
        function parseOCRText(text) {
            const result = { amount: '', date: '', merchant: '' };
            
            // Extract amount
            const amountMatch = text.match(/(\d{1,3}(?:[.,]\d{2,})?)/);
            if (amountMatch) {
                result.amount = amountMatch[1].replace(',', '');
            }
            
            // Extract date
            const dateMatch = text.match(/(\d{1,2}[\/\-\.]\d{1,2}[\/\-\.]\d{2,4})/);
            if (dateMatch) {
                result.date = dateMatch[1];
            }
            
            // Extract merchant
            const lines = text.split('\n');
            for (const line of lines) {
                const trimmed = line.trim();
                if (trimmed.length > 3 && !/^\d/.test(trimmed) && !/total|subtotal|tax/i.test(trimmed)) {
                    result.merchant = trimmed;
                    break;
                }
            }
            
            return result;
        }

        // Submit expense form
        document.getElementById('expenseForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            const submitBtn = document.getElementById('submitBtn');
            const submitBtnText = document.getElementById('submitBtnText');
            const submitBtnSpinner = document.getElementById('submitBtnSpinner');
            
            // Show loading state
            submitBtn.disabled = true;
            submitBtnText.textContent = 'Submitting...';
            submitBtnSpinner.classList.remove('hidden');
            
            try {
                const response = await fetch('/Expense_management/api/expenses.php?action=submit', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showSuccess('Expense submitted successfully!');
                    hideSubmitForm();
                    loadExpenses();
                } else {
                    throw new Error(result.error || 'Failed to submit expense');
                }
            } catch (error) {
                showError('Failed to submit expense: ' + error.message);
            } finally {
                // Reset button state
                submitBtn.disabled = false;
                submitBtnText.textContent = 'Submit Expense';
                submitBtnSpinner.classList.add('hidden');
            }
        });

        // Show currency conversion preview
        document.getElementById('original_amount').addEventListener('input', updateConversionPreview);
        document.getElementById('original_currency').addEventListener('change', updateConversionPreview);

        async function updateConversionPreview() {
            const amount = document.getElementById('original_amount').value;
            const currency = document.getElementById('original_currency').value;
            
            if (!amount || !currency) {
                document.getElementById('conversionPreview').classList.add('hidden');
                return;
            }
            
            // This would normally call the conversion API
            // For demo purposes, we'll show a placeholder
            document.getElementById('conversionText').textContent = 
                `Will be converted to company currency (${currentUser?.company_currency || 'USD'})`;
            document.getElementById('conversionPreview').classList.remove('hidden');
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
        loadExpenses();
    </script>
</body>
</html>
