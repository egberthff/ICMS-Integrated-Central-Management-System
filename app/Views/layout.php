<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'ICMS' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }

        .sidebar {
            min-height: 100vh;
            background-color: #2c3e50;
        }

        .sidebar a {
            color: #ecf0f1;
            padding: 12px 20px;
            display: block;
            border-left: 3px solid transparent;
            transition: all 0.3s;
        }

        .sidebar a:hover {
            background-color: #34495e;
            border-left-color: #3498db;
            color: #3498db;
        }

        .sidebar a.active {
            background-color: #3498db;
            border-left-color: #2980b9;
            color: white;
        }

        .main-content {
            background-color: white;
            min-height: 100vh;
        }

        .card {
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #5568d3 0%, #6a3a8b 100%);
        }

        .table-hover tbody tr:hover {
            background-color: #f5f5f5;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-md-block sidebar">
                <div class="position-sticky pt-3">
                    <div class="px-3 mb-4">
                        <h5 class="text-white"><i class="bi bi-shield-lock"></i> ICMS</h5>
                    </div>
                    <ul class="nav flex-column">
                        <?php
                        $menus = $menus ?? [];
                        foreach ($menus as $menu):
                            // Handle separator type menus
                            if (isset($menu['type']) && $menu['type'] === 'separator'):
                                ?>
                                <hr class="bg-secondary my-2">
                                <?php
                            else:
                                ?>
                                <li class="nav-item">
                                    <a class="nav-link <?= (strpos(current_url(), $menu['url']) !== false) ? 'active' : '' ?>"
                                        href="<?= $menu['url'] ?? '#' ?>" <?php if (isset($menu['onclick'])): ?>onclick="<?= $menu['onclick'] ?>" <?php endif; ?>>
                                        <i class="<?= $menu['icon'] ?? '' ?>"></i> <?= $menu['label'] ?? '' ?>
                                    </a>
                                </li>
                                <?php
                            endif;
                        endforeach;
                        ?>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-10 ms-sm-auto px-md-4 main-content">
                <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><?= $title ?? 'Dashboard' ?></h1>
                    <div>
                        <span class="me-3" id="userInfo">User: <strong id="currentUser">Loading...</strong></span>
                    </div>
                </div>

                <!-- Alert Messages -->
                <div id="alertContainer"></div>

                <!-- Page Content -->
                <div class="mt-4">
                    <?= $this->renderSection('content') ?>
                </div>
            </main>
        </div>
    </div>

    <?= $this->renderSection('extra_scripts') ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function getAuthToken() {
            return localStorage.getItem('authToken');
        }

        function setAuthToken(token) {
            localStorage.setItem('authToken', token);
        }

        function setUserInfo(username, activeRole) {
            document.getElementById('currentUser').textContent = `${username} (${activeRole})`;
            localStorage.setItem('username', username);
            localStorage.setItem('activeRole', activeRole);
        }

        function logout() {
            localStorage.removeItem('authToken');
            localStorage.removeItem('username');
            localStorage.removeItem('activeRole');
            // Clear cookie
            document.cookie = "authToken=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT;";
            window.location.href = '/';
        }

        function showAlert(message, type = 'success') {
            const alertHTML = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            const container = document.getElementById('alertContainer');
            if (container) {
                container.innerHTML = alertHTML;
                setTimeout(() => {
                    container.innerHTML = '';
                }, 5000);
            }
        }

        function apiCall(url, method = 'GET', data = null) {
            const options = {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + getAuthToken()
                }
            };

            if (data) {
                options.body = JSON.stringify(data);
            }

            return fetch(url, options)
                .then(response => {
                    if (response.status === 401) {
                        logout();
                        throw new Error('Unauthorized');
                    }
                    return response.json().then(data => ({
                        ok: response.ok,
                        status: response.status,
                        data: data
                    }));
                })
                .catch(error => {
                    showAlert('API Error: ' + error.message, 'danger');
                    throw error;
                });
        }

        // Initialize user info on page load
        window.addEventListener('load', function () {
            const username = localStorage.getItem('username') || 'User';
            const activeRole = localStorage.getItem('activeRole') || 'employee';
            setUserInfo(username, activeRole);
        });
    </script>
</body>

</html>