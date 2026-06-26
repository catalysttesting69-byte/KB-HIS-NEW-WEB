<?php
/**
 * admin/login.php
 * ─────────────────────────────────────────────────────────────
 * EXACT UI REPLICA FROM ROOT PROJECT
 * ─────────────────────────────────────────────────────────────
 */

session_start();
require_once __DIR__ . '/../includes/config.php';

// If admin is already logged in, redirect to dashboard
if (!empty($_SESSION['admin_id'])) {
    header('Location: ' . BASE_URL . '/admin/dashboard.php');
    exit;
}

require_once __DIR__ . '/../includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        $stmt = $pdo->prepare("SELECT id, name, password_hash FROM users WHERE email = :email AND role = 'admin' LIMIT 1");
        $stmt->execute([':email' => $email]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id']   = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['last_activity'] = time(); // Initialize activity
            header('Location: ' . BASE_URL . '/admin/dashboard.php');
            exit;
        } else {
            $error = 'Invalid email or password. Please try again.';
        }
    }
}

// Check for expiration message from redirect
if (isset($_GET['error']) && $_GET['error'] === 'session_expired') {
    $error = 'Your session has expired due to 30 minutes of inactivity. Please sign in again.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Portal — Zanzibar Safari</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --brand-dark: #0f172a;
            --brand-darker: #020617;
            --brand-accent: #d4af37;
            --brand-accent-glow: rgba(212, 175, 55, 0.5);
            --white: #ffffff;
            --text-muted: #94a3b8;
            --glass-bg: rgba(15, 23, 42, 0.4);
            --glass-border: rgba(255, 255, 255, 0.08);
        }
        
        body {
            font-family: 'Outfit', sans-serif;
            background: var(--brand-darker);
            min-height: 100vh;
            display: flex; 
            align-items: center; 
            justify-content: center; 
            padding: 20px;
            overflow: hidden;
            position: relative;
            color: var(--white);
        }

        /* Cinematic Background Layer */
        .bg-container {
            position: absolute;
            inset: 0;
            z-index: -1;
            overflow: hidden;
        }

        .bg-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            animation: slow-pan 30s infinite alternate linear;
            filter: brightness(0.7) contrast(1.1);
        }

        @keyframes slow-pan {
            0% { transform: scale(1.1) translate(0, 0); }
            100% { transform: scale(1.1) translate(-2%, -2%); }
        }

        /* Animated Gradient Overlay */
        .bg-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(2, 6, 23, 0.9) 0%, rgba(15, 23, 42, 0.6) 50%, rgba(2, 6, 23, 0.9) 100%);
            background-size: 200% 200%;
            animation: gradient-shift 15s ease infinite;
        }

        @keyframes gradient-shift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Floating Orbs for Extra Depth */
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.4;
            animation: float-orb 20s infinite ease-in-out;
            z-index: -1;
        }
        .orb-1 {
            width: 300px; height: 300px;
            background: var(--brand-accent);
            top: -100px; left: -100px;
        }
        .orb-2 {
            width: 400px; height: 400px;
            background: #2563eb;
            bottom: -150px; right: -100px;
            animation-delay: -5s;
        }

        @keyframes float-orb {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(30px, 50px); }
        }

        /* Modern Glassmorphic Card */
        .login-wrapper {
            position: relative;
            width: 100%;
            max-width: 480px;
            perspective: 1000px;
        }

        .login-card {
            background: var(--glass-bg);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid var(--glass-border);
            border-radius: 32px; 
            padding: 48px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7), inset 0 1px 0 rgba(255, 255, 255, 0.1);
            position: relative; 
            transform-style: preserve-3d;
            animation: card-entrance 1s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
            transform: translateY(40px) rotateX(10deg);
        }

        @keyframes card-entrance {
            to {
                opacity: 1;
                transform: translateY(0) rotateX(0);
            }
        }

        /* Premium Logo Area */
        .brand-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo-container {
            width: 88px;
            height: 88px;
            margin: 0 auto 24px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-container::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 50%;
            padding: 2px;
            background: linear-gradient(135deg, var(--brand-accent), transparent 60%);
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            animation: spin-border 4s linear infinite;
        }

        @keyframes spin-border {
            100% { transform: rotate(360deg); }
        }

        .logo-container img {
            width: 56px;
            height: auto;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.3));
            transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .logo-container:hover img {
            transform: scale(1.1);
        }

        .brand-header h1 { 
            font-size: 26px; 
            font-weight: 600; 
            letter-spacing: -0.02em;
            margin-bottom: 6px;
            background: linear-gradient(to right, #fff, #cbd5e1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .brand-header p { 
            color: var(--brand-accent); 
            font-size: 12px; 
            text-transform: uppercase;
            letter-spacing: 0.2em;
            font-weight: 500;
        }

        /* Advanced Input Groups */
        .form-group {
            position: relative;
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            transition: color 0.3s;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .input-wrapper::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 2px;
            background: var(--brand-accent);
            transform: scaleX(0);
            transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            transform-origin: left;
        }

        .input-wrapper:focus-within {
            background: rgba(255, 255, 255, 0.06);
            border-color: rgba(255, 255, 255, 0.15);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        }

        .input-wrapper:focus-within::after {
            transform: scaleX(1);
        }

        .input-wrapper i.fa-user, .input-wrapper i.fa-lock {
            position: absolute;
            left: 20px;
            font-size: 16px;
            color: var(--text-muted);
            transition: color 0.3s, transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .input-wrapper:focus-within i.fa-user, .input-wrapper:focus-within i.fa-lock {
            color: var(--brand-accent);
            transform: scale(1.1);
        }

        .input-wrapper input {
            width: 100%;
            background: transparent;
            border: none;
            color: var(--white);
            font-family: 'Outfit', sans-serif;
            font-size: 15px;
            padding: 18px 20px 18px 52px;
            outline: none;
        }

        .input-wrapper input::placeholder {
            color: rgba(255, 255, 255, 0.2);
        }

        /* Autofill Hardening */
        input:-webkit-autofill,
        input:-webkit-autofill:hover, 
        input:-webkit-autofill:focus, 
        input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 1000px #131b2f inset !important;
            -webkit-text-fill-color: var(--white) !important;
            transition: background-color 5000s ease-in-out 0s;
        }

        .toggle-password { 
            position: absolute; 
            right: 20px; 
            color: var(--text-muted); 
            cursor: pointer; 
            font-size: 16px;
            padding: 5px;
            transition: color 0.2s;
        }
        
        .toggle-password:hover {
            color: var(--white);
        }

        /* Error Messaging */
        .error-box { 
            background: rgba(239, 68, 68, 0.1); 
            border-left: 3px solid #ef4444; 
            border-radius: 8px; 
            color: #fca5a5; 
            font-size: 13px; 
            padding: 14px 16px; 
            margin-bottom: 24px; 
            display: flex; 
            align-items: center; 
            gap: 12px; 
            animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
        }

        @keyframes shake {
            10%, 90% { transform: translate3d(-1px, 0, 0); }
            20%, 80% { transform: translate3d(2px, 0, 0); }
            30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
            40%, 60% { transform: translate3d(4px, 0, 0); }
        }

        /* Premium Button */
        .btn-submit {
            position: relative;
            width: 100%;
            background: linear-gradient(135deg, var(--brand-accent), #b49122);
            color: var(--brand-darker);
            border: none;
            border-radius: 16px;
            font-family: 'Outfit', sans-serif;
            font-size: 15px;
            font-weight: 700;
            padding: 18px;
            margin-top: 12px;
            cursor: pointer;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .btn-submit::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.6s ease;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px var(--brand-accent-glow);
        }

        .btn-submit:hover::before {
            left: 100%;
        }

        .btn-submit i {
            transition: transform 0.3s ease;
        }
        .btn-submit:hover i {
            transform: translateX(4px);
        }

        /* Footer Links */
        .card-footer {
            margin-top: 32px;
            text-align: center;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            padding-top: 24px;
        }

        .back-link { 
            color: var(--text-muted); 
            font-size: 13px; 
            text-decoration: none; 
            display: inline-flex; 
            align-items: center; 
            gap: 8px; 
            font-weight: 500;
            transition: color 0.3s;
        }

        .back-link i {
            transition: transform 0.3s;
        }

        .back-link:hover { 
            color: var(--white); 
        }

        .back-link:hover i {
            transform: translateX(-4px);
        }

        /* Mobile Adjustments */
        @media (max-width: 480px) {
            .login-card {
                padding: 40px 24px;
                border-radius: 28px;
            }
            .brand-header h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>

<div class="bg-container">
    <div class="bg-overlay"></div>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <img src="https://lh3.googleusercontent.com/d/1K2ACMKsW0UDIqHkvl3MlM4gBq_l1BXd3" class="bg-image" alt="Zanzibar">
</div>

<div class="login-wrapper">
    <div class="login-card">
        <div class="brand-header">
            <div class="logo-container">
                <img src="../pictures/zanzibarSafarilogo.png" alt="Zanzibar Safari">
            </div>
            <h1>Admin Portal</h1>
            <p>Secure Access</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error-box">
                <i class="fas fa-shield-alt"></i> 
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Admin Email</label>
                <div class="input-wrapper">
                    <i class="far fa-user"></i>
                    <input type="email" name="email" placeholder="admin@zanzibar.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label>Security Key</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                    <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <span>Authenticate</span>
                <i class="fas fa-arrow-right"></i>
            </button>
        </form>
        
        <div class="card-footer">
            <a href="../index.html" class="back-link">
                <i class="fas fa-arrow-left"></i> Return to Main Website
            </a>
        </div>
    </div>
</div>

<script>
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('togglePassword');
    
    toggleIcon.addEventListener('click', () => {
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    });

    // Subtly track mouse movement to create 3D card tilt effect
    const wrapper = document.querySelector('.login-wrapper');
    const card = document.querySelector('.login-card');

    wrapper.addEventListener('mousemove', (e) => {
        const rect = wrapper.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;
        
        const rotateX = ((y - centerY) / centerY) * -5;
        const rotateY = ((x - centerX) / centerX) * 5;
        
        card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.02, 1.02, 1.02)`;
        card.style.transition = 'none';
    });

    wrapper.addEventListener('mouseleave', () => {
        card.style.transform = `perspective(1000px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)`;
        card.style.transition = 'transform 0.5s cubic-bezier(0.16, 1, 0.3, 1)';
    });
</script>
</body>
</html>
