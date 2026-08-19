<?php
$error = $_GET['error'] ?? '';
$usernameValue = $_GET['username'] ?? '';

$message = '';
$messageType = 'error';

if ($error === 'user') {
    $message = 'User does not exist.';
}
elseif ($error === 'password') {
    $message = 'Invalid Password.';
}
elseif ($error === 'pending') {
    $message = 'Account pending approval. Please wait for admin verification.';
    $messageType = 'warning';
}
elseif ($error === 'empty') {
    $message = 'Please enter both username and password.';
}
elseif ($error === 'system') {
    $message = 'System error. Please try again later.';
}
elseif (isset($_GET['registered'])) {
    $message = 'Account created. You can now login.';
    $messageType = 'success';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventIntel - Login</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        :root {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color-scheme: light;
        }
        *, *::before, *::after {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            min-height: 100vh;
            background: #f2f3f7;
            color: #222;
        }
        .container {
            display: flex;
            min-height: 100vh;
        }
        .left-panel,
        .right-panel {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px;
        }
        .left-panel {
            background: #ffffff;
            color: #222222;
        }
        .brand-wrapper {
            max-width: 560px;
        }
        .brand-title {
            margin: 0;
            font-size: clamp(3rem, 5vw, 5.2rem);
            line-height: 0.95;
            letter-spacing: -1px;
            color: #d4af37;
            font-weight: 800;
        }
        .brand-tagline {
            margin: 24px 0 0;
            font-size: 1.05rem;
            line-height: 1.9;
            color: #5f5f6f;
            max-width: 560px;
        }
        .right-panel {
            background: #dadada;
        }
        .login-card {
            width: min(100%, 430px);
            background: #f1f1f1;
            border-radius: 28px;
            box-shadow: 0 28px 80px rgba(15, 15, 15, 0.12);
            padding: 44px 40px;
        }
        .welcome-title {
            margin: 0 0 12px;
            font-size: 2.4rem;
            color: #212121;
        }
        .login-description {
            margin: 0 0 28px;
            color: #6c6c75;
            line-height: 1.7;
            font-size: 0.96rem;
        }
        .alert {
            margin: 0 0 22px;
            padding: 16px 18px;
            border-radius: 16px;
            font-size: 0.95rem;
            line-height: 1.5;
        }
        .alert.error {
            background: #ffe9e9;
            color: #7b1f1f;
            border: 1px solid #f5c4c4;
        }
        .alert.success {
            background: #e8f8eb;
            color: #1f5f35;
            border: 1px solid #bde3c4;
        }
        .alert.warning {
            background: #fff5d6;
            color: #7b5f18;
            border: 1px solid #f3d786;
        }
        .login-form {
            display: grid;
            gap: 18px;
        }
        .input-group {
            position: relative;
        }
        .input-wrapper {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            min-height: 54px;
            border-radius: 16px;
            border: 1px solid #d5d7df;
            background: #f8f9fb;
            padding: 0 14px;
        }
        .input-wrapper:focus-within {
            border-color: #c1b06f;
            box-shadow: 0 0 0 4px rgba(212, 175, 55, 0.12);
        }
        .icon {
            color: #9a9aa5;
            font-size: 1rem;
            width: 24px;
            text-align: center;
        }
        .input-field {
            flex: 1;
            width: 100%;
            border: none;
            background: transparent;
            padding: 14px 0;
            font-size: 1rem;
            color: #232323;
            outline: none;
        }
        .input-field::placeholder {
            color: #a2a2ad;
        }
        .toggle-password {
            color: #8f8f9b;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
        }
        .login-button {
            width: 100%;
            padding: 16px 18px;
            border: none;
            border-radius: 16px;
            background: linear-gradient(135deg, #e0b83f, #c78d12);
            color: #ffffff;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        .login-button:hover {
            transform: translateY(-1px);
        }
        .signup-footer {
            margin-top: 22px;
            text-align: center;
            font-size: 0.96rem;
            color: #5f5f6f;
        }
        .signup-footer a {
            color: #b48f14;
            font-weight: 700;
            text-decoration: none;
        }
        .signup-footer a:hover {
            text-decoration: underline;
        }
        @media (max-width: 920px) {
            .container {
                flex-direction: column;
            }
            .left-panel,
            .right-panel {
                padding: 32px 24px;
            }
            .left-panel {
                order: 2;
            }
            .right-panel {
                order: 1;
            }
        }
        @media (max-width: 640px) {
            .left-panel {
                padding: 28px 20px;
            }
            .brand-title {
                font-size: 3.2rem;
            }
            .brand-tagline {
                font-size: 0.98rem;
            }
            .login-card {
                padding: 30px 22px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left-panel">
            <div class="brand-wrapper">
                <h1 class="brand-title">EventIntel</h1>
                <p class="brand-tagline">EventIntel guides every decision with smart recommendations and AI generated event flow.</p>
            </div>
        </div>

        <div class="right-panel">
            <div class="login-card">
                <h1 class="welcome-title">Welcome!</h1>
                <p class="login-description"></p>

                <?php if ($message): ?>
                    <div class="alert <?= htmlspecialchars($messageType) ?>">
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>

                <form class="login-form" action="../auth/login.php" method="POST">
                    <div class="input-group">
                        <div class="input-wrapper">
                            <i class="icon fas fa-user"></i>
                            <input
                                type="text"
                                name="username"
                                placeholder="Username or Email"
                                class="input-field"
                                required
                                autocomplete="username"
                                value="<?= htmlspecialchars($usernameValue) ?>"
                            >
                        </div>
                    </div>

                    <div class="input-group">
                        <div class="input-wrapper">
                            <i class="icon fas fa-lock"></i>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                placeholder="Password"
                                class="input-field"
                                required
                                autocomplete="current-password"
                            >
                            <span class="toggle-password"><i class="fas fa-eye"></i></span>
                        </div>
                    </div>

                    <button type="submit" class="login-button">Login</button>
                </form>

                <div class="signup-footer">
                    Don't have an account? <a href="signup.php">Sign Up</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        const togglePassword = document.querySelector('.toggle-password');
        const passwordInput = document.querySelector('#password');

        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', () => {
                const type = passwordInput.type === 'password' ? 'text' : 'password';
                passwordInput.type = type;
                togglePassword.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
            });
        }
    </script>
</body>
</html>
