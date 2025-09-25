<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login Admin</title>
    <link rel="stylesheet" href="{{ asset('css/stel.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>
<body class="login-page">
    <div class="card-container">
        <div class="card">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="login-logo">
            <div class="form-section">
                <form id="admin-login-form">
                    <div class="form-group">
                        <label for="email">Email Admin</label>
                        <input type="email" id="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" class="form-control" required>
                    </div>
                    <button type="submit" class="submit-btn">Masuk sebagai Admin</button>
                </form>
            </div>
        </div>
    </div>
    <script>
        document.getElementById('admin-login-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            try {
                const response = await fetch('/api/login/admin', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email: email, password: password })
                });

                const data = await response.json();
                if (response.ok) {
                    sessionStorage.setItem('api_token', data.api_token);
                    sessionStorage.setItem('is_admin', 'true');
                    window.location.href = '/admin';
                } else {
                    alert('Login gagal: ' + data.message);
                }
            } catch (error) {
                alert('Terjadi kesalahan saat login.');
            }
        });
    </script>
</body>
</html>
