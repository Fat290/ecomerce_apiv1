<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - E-Commerce Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-8 md:p-10">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-500 rounded-full mb-4">
                <i class="fas fa-shield-alt text-white text-2xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Admin Login</h1>
            <p class="text-gray-500">Enter your credentials to access the admin panel</p>
        </div>

        <form id="login-form" class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-envelope mr-2 text-gray-400"></i>Email Address
                </label>
                <input
                    type="email"
                    id="email"
                    required
                    class="w-full border-2 border-gray-200 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all"
                    placeholder="admin@example.com"
                >
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-lock mr-2 text-gray-400"></i>Password
                </label>
                <input
                    type="password"
                    id="password"
                    required
                    class="w-full border-2 border-gray-200 rounded-lg px-4 py-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all"
                    placeholder="Enter your password"
                >
            </div>
            <button
                type="submit"
                id="login-btn"
                class="w-full bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-semibold py-3 rounded-lg hover:from-blue-600 hover:to-indigo-700 transition-all transform hover:scale-[1.02] shadow-lg hover:shadow-xl flex items-center justify-center space-x-2"
            >
                <span id="login-text">Login</span>
                <i id="login-spinner" class="fas fa-spinner fa-spin hidden"></i>
            </button>
        </form>

        <div id="error-message" class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm hidden flex items-center space-x-2">
            <i class="fas fa-exclamation-circle"></i>
            <span id="error-text"></span>
        </div>
    </div>

    <script>
        const apiBaseUrl = window.location.origin + '/api';
        const loginBtn = document.getElementById('login-btn');
        const loginText = document.getElementById('login-text');
        const loginSpinner = document.getElementById('login-spinner');
        const errorDiv = document.getElementById('error-message');
        const errorText = document.getElementById('error-text');

        function setLoading(loading) {
            if (loading) {
                loginBtn.disabled = true;
                loginText.textContent = 'Logging in...';
                loginSpinner.classList.remove('hidden');
                loginBtn.classList.add('opacity-75', 'cursor-not-allowed');
            } else {
                loginBtn.disabled = false;
                loginText.textContent = 'Login';
                loginSpinner.classList.add('hidden');
                loginBtn.classList.remove('opacity-75', 'cursor-not-allowed');
            }
        }

        function showError(message) {
            errorText.textContent = message;
            errorDiv.classList.remove('hidden');
            setTimeout(() => {
                errorDiv.classList.add('hidden');
            }, 5000);
        }

        document.getElementById('login-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            setLoading(true);
            errorDiv.classList.add('hidden');

            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            try {
                const response = await fetch(`${apiBaseUrl}/auth/login`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ email, password })
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({ message: 'Network error occurred' }));
                    showError(errorData.message || `Error: ${response.status} ${response.statusText}`);
                    setLoading(false);
                    return;
                }

                const data = await response.json();

                if (data.success && data.data) {
                    if (data.data.user.role !== 'admin') {
                        showError('Access denied. Admin role required.');
                        setLoading(false);
                        return;
                    }

                    localStorage.setItem('access_token', data.data.access_token);
                    localStorage.setItem('refresh_token', data.data.refresh_token);

                    loginText.textContent = 'Success! Redirecting...';
                    setTimeout(() => {
                        window.location.href = '/admin/dashboard';
                    }, 500);
                } else {
                    showError(data.message || 'Login failed');
                    setLoading(false);
                }
            } catch (error) {
                showError('Network error: ' + error.message);
                setLoading(false);
            }
        });
    </script>
</body>
</html>
