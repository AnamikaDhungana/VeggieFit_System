<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | VeggieFit</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg:#f8f4ff;
            --sunset:linear-gradient(150deg,#ffd56f,#ff9b7b,#d458f2);
            --accent:#8c34d6;
            --muted:#6b6b6b;
            --card-shadow:0 20px 60px rgba(46,9,82,.15);
        }
        *{box-sizing:border-box;}
        body{
            margin:0;
            font-family:'Times New Roman', Times, serif;
            background:var(--bg);
            min-height:100vh;
            display:flex;
            justify-content:center;
            align-items:center;
            padding:32px 16px;
        }
        .app-shell{
            width:100%;
            max-width:1120px;
            background:#fff;
            border-radius:36px;
            box-shadow:0 32px 90px rgba(57,9,120,.18);
            overflow:visible ;
        }
        .content{display:flex;flex-wrap:nowrap;}
        .hero-pane{
            flex:0 0 58%;
            background:var(--sunset);
            color:#fff;
            padding:70px 80px 70px 70px;
            min-height:560px;
            position:relative;
            overflow:hidden;
        }
        .hero-pane::after,
        .hero-pane::before {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
        }
        .hero-pane::after {
            width: 460px;
            height: 460px;
            top: -160px;
            right: -180px;
        }
        .hero-pane::before {
            width: 300px;
            height: 300px;
            bottom: -140px;
            left: -70px;
        }
        .hero-pane .curve-clip {
            position: absolute;
            top: 0;
            right: -220px;
            width: 380px;
            height: 100%;
            background: #fff;
            border-top-left-radius: 70% 100%;
            border-bottom-left-radius: 70% 100%;
            z-index: 0;
        }
        .hero-pane h1{margin:0;font-size:42px;position:relative;z-index:1;}
        .hero-pane p{margin:18px 0 32px;font-size:17px;max-width:360px;line-height:1.6;position:relative;z-index:1;}
        .hero-illustration {
            width: 100%;
            max-width: 500px;
            position: relative;
            z-index: 1;
            margin-top: 20px;
        }
        .hero-illustration svg {
            width: 100%;
            height: auto;
            display: block;
        }
        .form-pane{
            flex:1;
            padding:70px 60px;
            background:#fff;
        }
        .form-card{
            background:#fff;
            border-radius:30px;
            box-shadow:var(--card-shadow);
            padding:34px 38px 44px;
        }
        .form-card h2{
            margin:0;
            font-size:28px;
            color:var(--accent);
        }
        .form-card span{
            display:block;
            margin-top:6px;
            color:var(--muted);
            font-size:15px;
        }
        .input-row{
            margin-top:18px;
            position:relative;
        }
        .input-row:has(.input-label) {
            margin-top: 24px;
        }
        .input-row:has(.input-label) .input-label {
            margin-top: 0;
        }
        .input-row svg.input-icon{
            width:18px;
            height:18px;
            position:absolute;
            left:18px;
            top:50%;
            transform:translateY(-50%);
            fill:#b39edc;
            z-index:1;
        }
        .input-row .password-icon {
            display: none; /* Hide lock icon for password fields */
        }
        .input-row input,
        .input-row select{
            width:100%;
            border-radius:16px;
            border:1.5px solid #e4d9ff;
            padding:14px 50px 14px 54px;
            font-size:15px;
            transition:0.3s ease;
        }
        /* Only password and confirm password */
#password,
#confirm_password {
    width: 100%;
    padding: 14px 50px 14px 14px; /* left for typing, right for eye icon */
    font-size: 15px;
    border-radius: 16px;
    border: 1.5px solid #e4d9ff;
    box-sizing: border-box;
    transition: 0.3s ease;
}

/* Eye icon for password fields */
#togglePassword,
#toggleConfirmPassword {
    width: 20px;
    height: 20px;
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    fill: #8c34d6;
    z-index: 2;
    transition: fill 0.2s ease;
}

#togglePassword:hover,
#toggleConfirmPassword:hover {
    fill: #6a1b9a;
}

        
        .input-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--muted);
            margin-bottom: 6px;
            margin-left: 2px;
        }
        .input-row input:focus,
        .input-row select:focus {
            border-color:var(--accent);
            outline:none;
            box-shadow:0 8px 22px rgba(140,52,214,0.2);
        }

        /* Friend-style validation: red when invalid, green when valid */
        .input-row input:invalid,
        .input-row select:invalid {
            border-color: red !important;
        }

        .input-row input:valid,
        .input-row select:valid {
            border-color: green !important;
        }

        
        .field-grid{
            display:grid;
            grid-template-columns:repeat(2,1fr);
            gap:14px;
        }

        /* Age and height inputs padding */
.input-row input[name="age"],
.input-row input[name="height_cm"] {
    padding-left: 45px;  
    padding-right: 14px; 
}

/* Make icons not block clicks */
.input-row svg.input-icon {
    pointer-events: none;
    left:12px;
}

        button{
            width:100%;
            margin-top:24px;
            padding:15px;
            border:none;
            border-radius:18px;
            font-size:16px;
            font-weight:600;
            color:#fff;
            background:linear-gradient(135deg,#ffb867,#ff7ea5,#a347f4);
            cursor:pointer;
        }
        .switch{
            text-align:center;
            margin-top:18px;
            font-size:14px;
        }
        .switch a{
            color:var(--accent);
            font-weight:600;
            text-decoration:none;
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
        @media(max-width:920px){
            .content{flex-direction:column;}
            .hero-pane,.form-pane{flex:1 1 100%;}
            .hero-illustration {
                margin: 0 auto;
            }
        }
        @media(max-width:540px){
            .field-grid{grid-template-columns:1fr;}
        }
    </style>
</head>

<body>
<div class="app-shell">
<main class="content">

<section class="hero-pane">
    <h1>Create Your Veggie Journey.</h1>
    <p>Balanced vegetarian dishes, progress tracking, and habit nudges, everything your weight-loss plan needs in one place.</p>
    <div class="hero-illustration" aria-hidden="true">
        <svg viewBox="0 0 520 360">
            <defs>
                <linearGradient id="regPlate" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" stop-color="#fff9f5"/>
                    <stop offset="100%" stop-color="#ffe0ef"/>
                </linearGradient>
                <linearGradient id="steamReg" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" stop-color="#fff" stop-opacity=".9"/>
                    <stop offset="100%" stop-color="#fff" stop-opacity="0"/>
                </linearGradient>
            </defs>
            <ellipse cx="250" cy="320" rx="220" ry="44" fill="rgba(255,255,255,0.35)"/>
            <path d="M70 200c22 80 90 132 200 132s178-52 200-132H70z" fill="url(#regPlate)" stroke="#ffd3f7" stroke-width="6"/>
            <ellipse cx="150" cy="240" rx="60" ry="34" fill="#98d874"/>
            <ellipse cx="250" cy="250" rx="58" ry="30" fill="#ffb38c"/>
            <ellipse cx="340" cy="238" rx="66" ry="34" fill="#ff90be"/>
            <ellipse cx="390" cy="260" rx="44" ry="22" fill="#7dd9b3"/>
            <path d="M120 180c-26-2-33-32-9-44 18-9 34-10 37-26 3-15-8-26-8-26" fill="none" stroke="url(#steamReg)" stroke-width="8" stroke-linecap="round"/>
            <path d="M220 160c-24-2-30-32-7-44 16-9 29-11 32-26 3-14-7-24-7-24" fill="none" stroke="url(#steamReg)" stroke-width="8" stroke-linecap="round"/>
            <path d="M320 180c-20-3-26-28-7-40 14-8 25-10 27-22 2-12-6-18-6-18" fill="none" stroke="url(#steamReg)" stroke-width="8" stroke-linecap="round"/>
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

<h2>Sign Up</h2>
<span>Enter your info to keep it going!</span>

<form action="validate_registration.php" method="POST">

<div class="input-row">
    <svg class="input-icon" viewBox="0 0 24 24">
        <path d="M12 12c2.7 0 4.9-2.2 4.9-4.9S14.7 2.2 12 2.2 7.1 4.4 7.1 7.1 9.3 12 12 12zm0 2.4c-3.1 0-9.2 1.6-9.2 4.8V22h18.4v-2.8c0-3.2-6.1-4.8-9.2-4.8z"/>
    </svg>
    <input type="text"
           name="username"
           placeholder="Full Name"
           pattern="[A-Z][a-z]+(\s[A-Z][a-z]+)*"
           title="Full name must start with a capital letter, and each word's first letter capitalized (min 2 words allowed)"
           required>
</div>

<div class="input-row">
    <svg class="input-icon" viewBox="0 0 24 24"><path d="M2 6.5A2.5 2.5 0 0 1 4.5 4h15A2.5 2.5 0 0 1 22 6.5v11A2.5 2.5 0 0 1 19.5 20h-15A2.5 2.5 0 0 1 2 17.5v-11zm2 .5v.3l8 5.1 8-5.1V7H4z"/></svg>
    <input type="email"
           name="email"
           placeholder="Email Address"
           maxlength="100"
           pattern="^[a-z0-9][a-zA-Z0-9._%+-]*@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$"
           title="Email must not start with a capital letter"
           required>
</div>

<div class="field-grid">
    <div class="input-row">
        
        <input type="password"
               id="password"
               name="password"
               placeholder="password"
               pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{8,}"
               title="Minimum 8 chars with uppercase, lowercase, number and special character"
               oninput="checkPasswordMatch()"
               required>
        <svg class="eye-toggle" id="togglePassword" onclick="togglePasswordVisibility('password', 'togglePassword')" viewBox="0 0 24 24">
            <path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/>
        </svg>
    </div>
    <div class="input-row">
      
        <input type="password"
               id="confirm_password"
               name="confirm_password"
               placeholder="confirm password"
               oninput="checkPasswordMatch()"
               required>
        <svg class="eye-toggle" id="toggleConfirmPassword" onclick="togglePasswordVisibility('confirm_password', 'toggleConfirmPassword')" viewBox="0 0 24 24">
            <path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/>
        </svg>
    </div>
</div>

<div class="input-row">
    <svg class="input-icon" viewBox="0 0 24 24"><path d="M12 2a5 5 0 0 0-5 5v2.5a3 3 0 0 1-.55 1.75L5 13v3h14v-3l-1.45-1.75A3 3 0 0 1 17 9.5V7a5 5 0 0 0-5-5z"/></svg>
    <select name="gender" required>
        <option value="">Select Gender</option>
        <option value="Male">Male</option>
        <option value="Female">Female</option>
    </select>
</div>

<div class="field-grid">
    <div class="input-row">
        <svg class="input-icon" viewBox="0 0 24 24">
            <path d="M12 2.5a4 4 0 0 0-4 4V9h8V6.5a4 4 0 0 0-4-4zM8 11v8h8v-8H8zm-2 8V11H4v10h16V11h-2v8H6z"/>
        </svg>
        <input type="number" name="age" placeholder="Age" min="10" max="100" required>
    </div>

    <div class="input-row">
        <svg class="input-icon" viewBox="0 0 24 24">
            <path d="M12 2C9.24 2 8 5.58 8 8.5V20h8V8.5C16 5.58 14.76 2 12 2zm0 2c.78 0 2 1.61 2 4.5V18h-4V8.5C10 5.61 11.22 4 12 4z"/>
        </svg>
       <input
    type="number"
    name="height_cm"
    placeholder="Height in cm (e.g. 165)"
    min="1"
    max="250"
    step="0.01"
    required
    oninput="validateHeight(this)">

    </div>
</div>


<div class="input-row">
    <svg class="input-icon" viewBox="0 0 24 24"><path d="M15.5 2h-7A2.5 2.5 0 0 0 6 4.5v15A2.5 2.5 0 0 0 8.5 22h7a2.5 2.5 0 0 0 2.5-2.5v-15A2.5 2.5 0 0 0 15.5 2zM8 6h8v12H8V6z"/></svg>
    <input type="number" name="weight_kg" placeholder="Enter weight in kg (e.g. 70)" min="25" max="300" step="0.1" required>
</div>

<div class="input-row">
    <svg class="input-icon" viewBox="0 0 24 24"><path d="M13 3c-4.97 0-9 4.03-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42A8.954 8.954 0 0 0 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.28 2.54.72-1.21-3.5-2.08V8H12z"/></svg>
    <select name="activity_level" required>
        <option value="">Select Activity Level</option>
        <option value="1.2">Sedentary - Little or no exercise</option>
        <option value="1.375">Lightly Active - Light exercise 1-3 days/week</option>
        <option value="1.55">Moderately Active - Moderate exercise 3-5 days/week</option>
        <option value="1.725">Very Active - Hard exercise 6-7 days/week</option>
    </select>
</div>

<button type="submit" name="register">Create Account</button>

</form>

<p class="switch">Already have an account? <a href="login.php">Login now</a></p>

<div class="mini-food" aria-hidden="true">
    <svg viewBox="0 0 200 120">
        <defs>
            <linearGradient id="miniBowl" x1="0%" y1="0%" x2="0%" y2="100%">
                <stop offset="0%" stop-color="#fff"/>
                <stop offset="100%" stop-color="#ffe6f8"/>
            </linearGradient>
        </defs>
        <ellipse cx="100" cy="100" rx="90" ry="18" fill="rgba(255,134,194,0.35)"/>
        <path d="M20 60c8 30 32 52 80 52s72-22 80-52H20z" fill="url(#miniBowl)" stroke="#ffd4f5" stroke-width="3"/>
        <circle cx="70" cy="78" r="12" fill="#95d67b"/>
        <circle cx="100" cy="84" r="14" fill="#f5c15d"/>
        <circle cx="130" cy="78" r="12" fill="#ff9eb0"/>
    </svg>
</div>
</div>
</section>

</main>
</div>

<!-- SCRIPTS -->
<script>

function validateHeight(input) {
    const value = parseFloat(input.value);

    if (value > 0 && value < 50) {
        input.dataset.originalPlaceholder = input.placeholder;
        input.placeholder = "Looks like feet entered — we'll auto convert to cm";
    } else {
        if (input.dataset.originalPlaceholder) {
            input.placeholder = input.dataset.originalPlaceholder;
        }
    }
}

function togglePasswordVisibility(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    
    if (input.type === 'password') {
        input.type = 'text';
        // Show normal eye (visible icon) when password is shown
        icon.innerHTML = '<path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>';
    } else {
        input.type = 'password';
        // Show eye with slash (hidden icon) when password is hidden
        icon.innerHTML = '<path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/>';
    }
}
</script>

<script>
function checkPasswordMatch() {
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    if (!password || !confirmPassword) return;

    if (confirmPassword.value === '') {
        confirmPassword.setCustomValidity('');
        return;
    }

    // If the main password doesn't meet the pattern rules, mark confirm as invalid too.
    if (!password.checkValidity()) {
        confirmPassword.setCustomValidity('Password strength is not valid.');
        return;
    }

    confirmPassword.setCustomValidity(
        password.value === confirmPassword.value ? '' : 'Passwords do not match.'
    );
}

document.addEventListener('DOMContentLoaded', function () {
    checkPasswordMatch();
});
</script>

</body>
</html>