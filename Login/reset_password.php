<?php
session_start();
require '../db_connection.php';

if (!isset($_GET['token'])) {
    die("Invalid request.");
}

$token = $_GET['token'];

// Validate token and expiry
$stmt = $conn->prepare("SELECT * FROM users WHERE reset_token = :token LIMIT 1");
$stmt->execute([':token' => $token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if user exists and token not expired
if (!$user || strtotime($user['reset_token_expiry']) < time()) {
    die("Invalid or expired token.");
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Nutrition Planner</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
   :root {
    --bg: #f8f4ff;
    --sunset: linear-gradient(135deg, #ffb562, #ff7380, #b24ce4);
    --accent: #8c34d6;
    --accent-dark: #5a189a;
    --muted: #686868;
    --card-shadow: 0 20px 60px rgba(46, 9, 82, 0.15);
}

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: 'Times New Roman', Times, serif;
    background: var(--bg);
    color: #1f1a2b;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 32px 16px;
}

.app-shell {
    width: 100%;
    max-width: 1100px;
    background: #fff;
    border-radius: 36px;
    box-shadow: 0 30px 80px rgba(57, 9, 120, 0.18);
    overflow: hidden;
}

.content {
    display: flex;
    flex-wrap: nowrap;
    position: relative;
    overflow: hidden;
}

.hero-pane {
    flex: 0 0 58%;
    background: var(--sunset);
    padding: 70px 80px 70px 70px;
    color: #fff;
    position: relative;
    overflow: hidden;
    min-height: 560px;
}

.hero-pane::after,
.hero-pane::before {
    content: '';
    position: absolute;
    border-radius: 50%;
}

.hero-pane::after {
    width: 460px;
    height: 460px;
    background: rgba(255, 255, 255, 0.14);
    top: -160px;
    right: -150px;
}

.hero-pane::before {
    width: 320px;
    height: 320px;
    background: rgba(255, 255, 255, 0.18);
    bottom: -140px;
    left: -60px;
}

.hero-pane h1 {
    font-size: 42px;
    margin: 0;
}

.hero-pane p {
    font-size: 17px;
    margin: 16px 0 30px;
    max-width: 360px;
    line-height: 1.6;
}

.hero-pane > *:not(.curve-clip) {
    position: relative;
    z-index: 1;
}

.hero-pane .curve-clip {
    position: absolute;
    top: 0;
    right: -200px;
    width: 360px;
    height: 100%;
    background: #fff;
    border-top-left-radius: 70% 100%;
    border-bottom-left-radius: 70% 100%;
    z-index: 0;
}

.hero-illustration {
    width: 100%;
    max-width: 500px;
    position: relative;
}

.hero-illustration svg {
    width: 100%;
    height: auto;
    display: block;
}

.form-pane {
    flex: 1 1 42%;
    padding: 70px 60px;
    background: #fff;
    position: relative;
}

.form-card {
    position: relative;
    background: #fff;
    border-radius: 28px;
    box-shadow: var(--card-shadow);
    padding: 34px 36px 38px;
    z-index: 1;
}

.form-card h2 {
    margin: 0;
    color: var(--accent);
    font-size: 28px;
}

.form-card span {
    display: block;
    margin-top: 6px;
    color: var(--muted);
    font-size: 15px;
}

.alert {
    border-radius: 14px;
    padding: 10px 14px;
    font-size: 14px;
    margin-top: 16px;
    text-align: center;
}

.alert.error {
    color: #b00020;
    background: #ffebee;
    border: 1px solid #ffcdd2;
}

.alert.success {
    color: #155724;
    background: #d4edda;
    border: 1px solid #c3e6cb;
}

.input-row {
    margin-top: 18px;
    position: relative;
}

.input-row svg {
    width: 18px;
    height: 18px;
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    fill: #b39edc;
}

.input-row input {
    width: 100%;
    border-radius: 16px;
    border: 1.5px solid #e4d9ff;
    padding: 14px 16px 14px 48px;
    font-size: 15px;
    background: #fff;
    transition: 0.3s ease;
}

.input-row input:focus {
    border-color: var(--accent);
    outline: none;
    box-shadow: 0 8px 22px rgba(178, 76, 228, 0.18);
}

button {
    width: 100%;
    border: none;
    margin-top: 20px;
    padding: 15px;
    border-radius: 18px;
    font-size: 16px;
    font-weight: 600;
    color: #fff;
    background: linear-gradient(135deg, #ffa45b, #ff6f91, #8c34d6);
    cursor: pointer;
    box-shadow: 0 15px 28px rgba(140, 52, 214, 0.35);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

button:hover {
    transform: translateY(-2px);
    box-shadow: 0 20px 34px rgba(140, 52, 214, 0.4);
}

.register-link {
    margin-top: 16px;
    text-align: center;
    color: var(--muted);
    font-size: 14px;
}

.register-link a {
    color: var(--accent);
    font-weight: 600;
    text-decoration: none;
}

.register-link a:hover {
    text-decoration: underline;
}

.mini-food {
    margin-top: 28px;
    display: flex;
    justify-content: center;
}

.mini-food svg {
    width: 150px;
    height: auto;
}

@media (max-width: 900px) {
    .content {
        flex-direction: column;
    }

    .hero-pane,
    .form-pane {
        flex: 1 1 100%;
    }

    .hero-illustration {
        margin: 0 auto;
    }
}
</style>
</head>
<body>
<div class="app-shell">
    <main class="content">
        <section class="hero-pane">
            <h1>Set a fresh password.</h1>
            <p>Keep your veggie goals on track with a quick password reset.</p>
            <div class="hero-illustration" aria-hidden="true">
                <svg viewBox="0 0 520 360">
                    <defs>
                        <linearGradient id="resetPlate" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" stop-color="#fff9f5"/>
                            <stop offset="100%" stop-color="#ffe0ef"/>
                        </linearGradient>
                        <linearGradient id="steamReset" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" stop-color="#fff" stop-opacity=".9"/>
                            <stop offset="100%" stop-color="#fff" stop-opacity="0"/>
                        </linearGradient>
                    </defs>
                    <ellipse cx="250" cy="320" rx="220" ry="44" fill="rgba(255,255,255,0.35)"/>
                    <path d="M70 200c22 80 90 132 200 132s178-52 200-132H70z" fill="url(#resetPlate)" stroke="#ffd3f7" stroke-width="6"/>
                    <ellipse cx="150" cy="240" rx="60" ry="34" fill="#98d874"/>
                    <ellipse cx="250" cy="250" rx="58" ry="30" fill="#ffb38c"/>
                    <ellipse cx="340" cy="238" rx="66" ry="34" fill="#ff90be"/>
                    <ellipse cx="390" cy="260" rx="44" ry="22" fill="#7dd9b3"/>
                    <path d="M120 180c-26-2-33-32-9-44 18-9 34-10 37-26 3-15-8-26-8-26" fill="none" stroke="url(#steamReset)" stroke-width="8" stroke-linecap="round"/>
                    <path d="M220 160c-24-2-30-32-7-44 16-9 29-11 32-26 3-14-7-24-7-24" fill="none" stroke="url(#steamReset)" stroke-width="8" stroke-linecap="round"/>
                    <path d="M320 180c-20-3-26-28-7-40 14-8 25-10 27-22 2-12-6-18-6-18" fill="none" stroke="url(#steamReset)" stroke-width="8" stroke-linecap="round"/>
                    <circle cx="205" cy="222" r="16" fill="#fff"/>
                    <circle cx="260" cy="230" r="16" fill="#fff"/>
                    <circle cx="320" cy="214" r="16" fill="#fff"/>
                    <path d="M420 150c-12 32-6 70-6 70l20 2 12-40 18 38 16-3s4-25-8-48c-10-18-22-29-31-29-9 0-15 4-21 10z" fill="#ffe9ba"/>
                    <path d="M170 200c-32 10-48 24-52 42-6 24 12 48 39 52 30 4 40-26 72-22 32 4 40 32 62 31 19-1 36-16 36-38 0-26-24-32-46-44-43-22-82-28-111-21z" fill="#ffc4df"/>
                </svg>
            </div>
            <div class="curve-clip" aria-hidden="true"></div>
        </section>

        <section class="form-pane">
            <div class="form-card">
                <h2>Reset Password</h2>
                <span>Choose a strong password to secure your account.</span>

                <?php if (isset($_SESSION['error'])): ?>
                    <p class="alert error"><?= htmlspecialchars($_SESSION['error']) ?></p>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <form action="update_password.php" method="POST">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                    <div class="input-row">
                        <svg viewBox="0 0 24 24"><path d="M17 8V6a5 5 0 0 0-10 0v2H5v14h14V8h-2zm-2 0H9V6a3 3 0 0 1 6 0v2z"/></svg>
                        <input type="password" name="password" placeholder="New Password" required>
                    </div>

                    <div class="input-row">
                        <svg viewBox="0 0 24 24"><path d="M17 8V6a5 5 0 0 0-10 0v2H5v14h14V8h-2zm-2 0H9V6a3 3 0 0 1 6 0v2z"/></svg>
                        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                    </div>

                    <button type="submit">Update Password</button>

                    <div class="register-link">
                        <p><a href="login.php">Back to Login</a></p>
                    </div>
                </form>

                <div class="mini-food" aria-hidden="true">
                    <svg viewBox="0 0 200 120">
                        <defs>
                            <linearGradient id="miniBowlReset" x1="0%" y1="0%" x2="0%" y2="100%">
                                <stop offset="0%" stop-color="#fff"/>
                                <stop offset="100%" stop-color="#ffe6f8"/>
                            </linearGradient>
                        </defs>
                        <ellipse cx="100" cy="100" rx="90" ry="18" fill="rgba(255,134,194,0.35)"/>
                        <path d="M20 60c8 30 32 52 80 52s72-22 80-52H20z" fill="url(#miniBowlReset)" stroke="#ffd4f5" stroke-width="3"/>
                        <circle cx="70" cy="78" r="12" fill="#95d67b"/>
                        <circle cx="100" cy="84" r="14" fill="#f5c15d"/>
                        <circle cx="130" cy="78" r="12" fill="#ff9eb0"/>
                    </svg>
                </div>
            </div>
        </section>
    </main>
</div>

<?php
$resetSuccess = $_SESSION['reset_success'] ?? null;
$resetError = $_SESSION['reset_error'] ?? null;
unset($_SESSION['reset_success'], $_SESSION['reset_error']);
?>
<script>
    (function() {
        const success = <?php echo $resetSuccess ? json_encode($resetSuccess) : 'null'; ?>;
        const error = <?php echo $resetError ? json_encode($resetError) : 'null'; ?>;
        if (success) {
            setTimeout(() => {
                alert(success);
                window.location.href = 'login.php';
            }, 400);
        } else if (error) {
            setTimeout(() => alert(error), 400);
        }
    })();
</script>
</body>
</html>