<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Manager - Sign Up</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-green-50 to-blue-100 min-h-screen flex items-center justify-center py-8">
    <div class="max-w-2xl w-full mx-4">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-green-600 rounded-full mb-4">
                    <i class="fas fa-building text-white text-2xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-gray-900">Create Your Company</h1>
                <p class="text-gray-600 mt-2">Set up your expense management system</p>
            </div>

            <form id="signupForm" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="company_name" class="block text-sm font-medium text-gray-700 mb-2">Company Name</label>
                        <div class="relative">
                            <input type="text" id="company_name" name="company_name" required
                                   class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition duration-200"
                                   placeholder="Enter company name">
                            <i class="fas fa-building absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                    </div>

                    <div>
                        <label for="country" class="block text-sm font-medium text-gray-700 mb-2">Country</label>
                        <div class="relative">
                            <select id="country" name="country" required
                                    class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition duration-200">
                                <option value="">Select Country</option>
                            </select>
                            <i class="fas fa-globe absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="currency" class="block text-sm font-medium text-gray-700 mb-2">Default Currency</label>
                    <div class="relative">
                        <select id="currency" name="currency" required
                                class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition duration-200">
                            <option value="">Select Currency</option>
                        </select>
                        <i class="fas fa-dollar-sign absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>

                <div class="border-t pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Admin Account</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="admin_name" class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                            <div class="relative">
                                <input type="text" id="admin_name" name="admin_name" required
                                       class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition duration-200"
                                       placeholder="Enter your full name">
                                <i class="fas fa-user absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            </div>
                        </div>

                        <div>
                            <label for="admin_email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                            <div class="relative">
                                <input type="email" id="admin_email" name="admin_email" required
                                       class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition duration-200"
                                       placeholder="Enter your email">
                                <i class="fas fa-envelope absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <label for="admin_password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                        <div class="relative">
                            <input type="password" id="admin_password" name="admin_password" required
                                   class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition duration-200"
                                   placeholder="Create a password">
                            <i class="fas fa-lock absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">Password must be at least 6 characters long</p>
                    </div>
                </div>

                <button type="submit" id="signupBtn"
                        class="w-full bg-green-600 text-white py-3 px-4 rounded-lg hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition duration-200 font-medium">
                    <span id="signupBtnText">Create Company & Account</span>
                    <i id="signupBtnSpinner" class="fas fa-spinner fa-spin ml-2 hidden"></i>
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-gray-600">Already have an account?</p>
                <a href="index.php" class="text-green-600 hover:text-green-700 font-medium transition duration-200">
                    Sign in here
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
        let countries = [];

        // Load countries on page load
        async function loadCountries() {
            try {
                const response = await fetch('https://restcountries.com/v3.1/all?fields=name,currencies');
                const data = await response.json();
                
                countries = data.map(country => ({
                    name: country.name.common,
                    currency: Object.keys(country.currencies || {})[0]
                })).filter(c => c.currency).sort((a, b) => a.name.localeCompare(b.name));
                
                const countrySelect = document.getElementById('country');
                const currencySelect = document.getElementById('currency');
                
                countries.forEach(country => {
                    const option = document.createElement('option');
                    option.value = country.name;
                    option.textContent = country.name;
                    countrySelect.appendChild(option);
                });
                
            } catch (error) {
                console.error('Error loading countries:', error);
            }
        }

        // Update currency when country changes
        document.getElementById('country').addEventListener('change', function() {
            const selectedCountry = this.value;
            const currencySelect = document.getElementById('currency');
            
            // Clear existing options
            currencySelect.innerHTML = '<option value="">Select Currency</option>';
            
            if (selectedCountry) {
                const country = countries.find(c => c.name === selectedCountry);
                if (country) {
                    const option = document.createElement('option');
                    option.value = country.currency;
                    option.textContent = country.currency;
                    currencySelect.appendChild(option);
                }
            }
        });

        document.getElementById('signupForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            const signupBtn = document.getElementById('signupBtn');
            const signupBtnText = document.getElementById('signupBtnText');
            const signupBtnSpinner = document.getElementById('signupBtnSpinner');
            const errorAlert = document.getElementById('errorAlert');
            const errorMessage = document.getElementById('errorMessage');
            
            // Show loading state
            signupBtn.disabled = true;
            signupBtnText.textContent = 'Creating Company...';
            signupBtnSpinner.classList.remove('hidden');
            errorAlert.classList.add('hidden');
            
            try {
                const response = await fetch('/Expense_management/api/simple_auth.php?action=signup', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Redirect to admin dashboard
                    window.location.href = 'dashboard_admin.php';
                } else {
                    throw new Error(result.error || 'Signup failed');
                }
            } catch (error) {
                errorMessage.textContent = error.message;
                errorAlert.classList.remove('hidden');
            } finally {
                // Reset button state
                signupBtn.disabled = false;
                signupBtnText.textContent = 'Create Company & Account';
                signupBtnSpinner.classList.add('hidden');
            }
        });

        // Load countries when page loads
        loadCountries();
    </script>
</body>
</html>
