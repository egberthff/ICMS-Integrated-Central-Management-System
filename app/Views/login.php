<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ICMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
        }

        .login-card {
            border: none;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-align: center;
            padding: 30px;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 10px;
            font-weight: bold;
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #5568d3 0%, #6a3a8b 100%);
            color: white;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="card login-card">
            <div class="login-header">
                <h1><i class="bi bi-shield-lock"></i> ICMS</h1>
                <p class="mb-0">Identity & Credential Management System</p>
            </div>
            <div class="card-body p-4">
                <form id="loginForm" method="POST" action="/login">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" autocomplete="username"
                            placeholder="jane.doe@company.com" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" autocomplete="current-password"
                            placeholder="Enter password" required>
                    </div>
                    <button type="submit" class="btn btn-login w-100 text-white mb-3">Login</button>
                </form>
                <div id="alertContainer"></div>
                <p class="text-muted small text-center mb-0">Demo: jane.doe@company.com / secret123</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showAlert(message, type = 'success') {
            const alertHTML = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            document.getElementById('alertContainer').innerHTML = alertHTML;
        }

        document.getElementById('loginForm').addEventListener('submit', async function (e) {
            e.preventDefault();

            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;

            try {
                const response = await fetch('/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ username, password })
                });

                const result = await response.json();

                if (response.ok) {
                    localStorage.setItem('username', result.data.user.username);
                    localStorage.setItem('activeRole', result.data.user.active_role);
                    localStorage.setItem('user_id', result.data.user.user_id);

                    // Set cookie for page loads
                    // document.cookie = "authToken=" + result.data.token + "; path=/; max-age=86400"; // 24 hours
                    window.location.href = '/dashboard';
                } else {
                    showAlert(result.message || 'Login failed', 'danger');
                }
            } catch (error) {
                showAlert('Error: ' + error.message, 'danger');
            }
        });

    </script>
</body>

</html>