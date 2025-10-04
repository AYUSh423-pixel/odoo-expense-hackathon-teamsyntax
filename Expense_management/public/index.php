<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Manager - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-600 rounded-full mb-4">
                    <i class="fas fa-receipt text-white text-2xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-gray-900">Expense Manager</h1>
                <p class="text-gray-600 mt-2">Sign in to your account</p>
            </div>

            <form id="loginForm" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                    <div class="relative">
                        <input type="email" id="email" name="email" required
                               class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                               placeholder="Enter your email">
                        <i class="fas fa-envelope absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <div class="relative">
                        <input type="password" id="password" name="password" required
                               class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                               placeholder="Enter your password">
                        <i class="fas fa-lock absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>

                <button type="submit" id="loginBtn"
                        class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200 font-medium">
                    <span id="loginBtnText">Sign In</span>
                    <i id="loginBtnSpinner" class="fas fa-spinner fa-spin ml-2 hidden"></i>
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-gray-600">Don't have an account?</p>
                <a href="signup.php" class="text-blue-600 hover:text-blue-700 font-medium transition duration-200">
                    Create a new company
                </a>
            </div>
        </div>

        <div id="errorAlert" class="hidden mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <span id="errorMessage"></span>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const loginBtn = document.getElementById('loginBtn');
            const loginBtnText = document.getElementById('loginBtnText');
            const loginBtnSpinner = document.getElementById('loginBtnSpinner');
            const errorAlert = document.getElementById('errorAlert');
            const errorMessage = document.getElementById('errorMessage');
            
            // Show loading state
            loginBtn.disabled = true;
            loginBtnText.textContent = 'Signing In...';
            loginBtnSpinner.classList.remove('hidden');
            errorAlert.classList.add('hidden');
            
            try {
                const response = await fetch('/Expense_management/api/simple_auth.php?action=login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ email, password })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Redirect based on role
                    const role = data.user.role;
                    if (role === 'Admin') {
                        window.location.href = 'dashboard_admin.php';
                    } else if (role === 'Manager') {
                        window.location.href = 'dashboard_manager.php';
                    } else if (role === 'Finance') {
                        window.location.href = 'dashboard_finance.php';
                    } else if (role === 'Director') {
                        window.location.href = 'dashboard_director.php';
                    } else if (role === 'HR') {
                        window.location.href = 'dashboard_hr.php';
                    } else if (role === 'CFO') {
                        window.location.href = 'dashboard_cfo.php';
                    } else {
                        window.location.href = 'dashboard_employee.php';
                    }
                } else {
                    throw new Error(data.error || 'Login failed');
                }
            } catch (error) {
                errorMessage.textContent = error.message;
                errorAlert.classList.remove('hidden');
            } finally {
                // Reset button state
                loginBtn.disabled = false;
                loginBtnText.textContent = 'Sign In';
                loginBtnSpinner.classList.add('hidden');
            }
        });
    </script>
</body>
</html>
