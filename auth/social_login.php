<?php
/**
 * Google Sign-In Simulation
 * Mimics the real Google OAuth sign-in UI/UX exactly.
 * Since this runs on XAMPP (no real OAuth), it simulates the full Google flow
 * and logs the user into their gym account via matching email in the DB.
 */
require_once('../config/functions.php');

$provider = $_GET['provider'] ?? 'google';

// Only handle Google for now (Facebook kept separate)
if ($provider !== 'google') {
    // Facebook: quick demo redirect to callback
    header('Location: social_callback.php?provider=facebook&role=member');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in – Google Accounts</title>
    <link rel="icon" href="https://www.google.com/favicon.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Google+Sans:wght@400;500;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Google Sans', 'Roboto', Arial, sans-serif;
            background: #f8f9fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* ── Main Card ── */
        .g-card {
            background: #fff;
            border-radius: 28px;
            box-shadow: 0 2px 10px rgba(0,0,0,.12), 0 6px 30px rgba(0,0,0,.06);
            max-width: 450px;
            width: 100%;
            padding: 48px 40px 36px;
            position: relative;
            overflow: hidden;
        }

        /* ── Google Logo ── */
        .g-logo {
            display: flex;
            justify-content: center;
            margin-bottom: 28px;
        }
        .g-logo svg { height: 24px; }

        /* ── Avatar Circle (Step 2) ── */
        .g-avatar {
            width: 52px; height: 52px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4285F4 0%, #34A853 100%);
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; font-weight: 700;
            color: white;
            margin: 0 auto 16px;
            user-select: none;
        }

        /* ── Title & Subtitle ── */
        .g-title {
            text-align: center;
            font-size: 24px;
            font-weight: 400;
            color: #202124;
            margin-bottom: 8px;
            line-height: 1.3;
        }
        .g-subtitle {
            text-align: center;
            font-size: 14px;
            color: #5f6368;
            margin-bottom: 28px;
            line-height: 1.5;
        }

        /* ── Email chip on Step 2 ── */
        .g-email-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 1px solid #dadce0;
            border-radius: 20px;
            padding: 6px 12px 6px 8px;
            font-size: 13px;
            color: #202124;
            cursor: pointer;
            margin: 0 auto 28px;
            transition: background 0.2s;
            display: none; /* shown on step 2 */
        }
        .g-email-chip:hover { background: #f1f3f4; }
        .g-email-chip svg { flex-shrink: 0; }
        .g-email-chip span { white-space: nowrap; max-width: 200px; overflow: hidden; text-overflow: ellipsis; }
        .g-email-chip i { font-size: 11px; color: #5f6368; }

        /* ── Input fields ── */
        .g-input-wrap {
            position: relative;
            margin-bottom: 8px;
        }
        .g-input {
            width: 100%;
            border: 1px solid #dadce0;
            border-radius: 4px;
            padding: 14px 16px;
            font-size: 16px;
            font-family: inherit;
            color: #202124;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            background: transparent;
        }
        .g-input:focus {
            border-color: #1a73e8;
            box-shadow: 0 0 0 2px rgba(26,115,232,.25);
        }
        .g-label {
            position: absolute;
            top: 50%;
            left: 16px;
            transform: translateY(-50%);
            font-size: 16px;
            color: #5f6368;
            pointer-events: none;
            transition: all 0.15s ease;
            background: white;
            padding: 0 3px;
        }
        .g-input:focus ~ .g-label,
        .g-input:not(:placeholder-shown) ~ .g-label {
            top: 0;
            font-size: 11px;
            color: #1a73e8;
        }
        .g-input-wrap.error .g-input { border-color: #d93025; }
        .g-input-wrap.error .g-label { color: #d93025 !important; }

        /* Show/hide password toggle */
        .g-pass-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #5f6368;
            font-size: 20px;
            line-height: 1;
            user-select: none;
        }
        .g-pass-toggle:hover { color: #202124; }

        /* ── Helper/Error text ── */
        .g-helper {
            font-size: 12px;
            color: #5f6368;
            margin-bottom: 20px;
            padding: 0 2px;
        }
        .g-error-msg {
            font-size: 12px;
            color: #d93025;
            margin-bottom: 20px;
            padding: 0 2px;
            display: none;
        }

        /* ── Links row ── */
        .g-link {
            font-size: 14px;
            color: #1a73e8;
            text-decoration: none;
            font-weight: 500;
        }
        .g-link:hover { text-decoration: underline; }

        /* ── Bottom action row ── */
        .g-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 32px;
        }

        /* ── Next / Confirm button ── */
        .g-btn-next {
            background: #1a73e8;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 10px 24px;
            font-size: 14px;
            font-weight: 500;
            font-family: inherit;
            cursor: pointer;
            transition: background 0.2s, box-shadow 0.2s;
            letter-spacing: .25px;
        }
        .g-btn-next:hover {
            background: #1765cc;
            box-shadow: 0 1px 3px rgba(0,0,0,.3);
        }
        .g-btn-next:active { background: #1558b0; }
        .g-btn-next:disabled {
            background: #c5d4f5;
            cursor: not-allowed;
            box-shadow: none;
        }

        /* ── Divider ── */
        .g-divider {
            height: 1px;
            background: #e8eaed;
            margin: 20px -40px;
        }

        /* ── "Use another account" ── */
        .g-another {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 0;
            font-size: 14px;
            color: #1a73e8;
            cursor: pointer;
            font-weight: 500;
        }
        .g-another:hover { text-decoration: underline; }

        /* ── Loading spinner overlay ── */
        .g-spinner-overlay {
            position: fixed;
            inset: 0;
            background: rgba(255,255,255,.85);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 999;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s;
        }
        .g-spinner-overlay.show { opacity: 1; pointer-events: all; }
        .g-spinner {
            width: 44px; height: 44px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #1a73e8;
            border-radius: 50%;
            animation: spin .8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── Step panels ── */
        .step { display: none; }
        .step.active { display: block; }

        /* ── Slide animation ── */
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(40px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        .step.active { animation: slideIn .25s ease; }

        /* ── Footer ── */
        .g-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 24px;
            font-size: 12px;
            color: #5f6368;
            flex-wrap: wrap;
            gap: 8px;
            max-width: 450px;
            width: 100%;
        }
        .g-footer a { color: #5f6368; text-decoration: none; margin: 0 6px; }
        .g-footer a:hover { text-decoration: underline; }

        /* ── "Not Google?" notice at bottom ── */
        .g-back-notice {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #80868b;
        }
        .g-back-notice a { color: #1a73e8; text-decoration: none; }
        .g-back-notice a:hover { text-decoration: underline; }

        @media (max-width: 480px) {
            .g-card { padding: 40px 24px 28px; border-radius: 20px; }
            .g-divider { margin: 20px -24px; }
        }
    </style>
</head>
<body>

<!-- Spinner overlay -->
<div class="g-spinner-overlay" id="spinnerOverlay">
    <div class="g-spinner"></div>
</div>

<!-- Main card -->
<div class="g-card">

    <!-- Google Logo (always shown) -->
    <div class="g-logo">
        <!-- Official Google "G" logo colours -->
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 272 92">
            <path fill="#EA4335" d="M115.75 47.18c0 12.77-9.99 22.18-22.25 22.18s-22.25-9.41-22.25-22.18C71.25 34.32 81.24 25 93.5 25s22.25 9.32 22.25 22.18zm-9.74 0c0-7.98-5.79-13.44-12.51-13.44S80.99 39.2 80.99 47.18c0 7.9 5.79 13.44 12.51 13.44s12.51-5.55 12.51-13.44z"/>
            <path fill="#FBBC05" d="M163.75 47.18c0 12.77-9.99 22.18-22.25 22.18s-22.25-9.41-22.25-22.18c0-12.85 9.99-22.18 22.25-22.18s22.25 9.32 22.25 22.18zm-9.74 0c0-7.98-5.79-13.44-12.51-13.44s-12.51 5.46-12.51 13.44c0 7.9 5.79 13.44 12.51 13.44s12.51-5.55 12.51-13.44z"/>
            <path fill="#4285F4" d="M209.75 26.34v39.82c0 16.38-9.66 23.07-21.08 23.07-10.75 0-17.22-7.19-19.66-13.07l8.48-3.53c1.51 3.61 5.21 7.87 11.17 7.87 7.31 0 11.84-4.51 11.84-13v-3.19h-.34c-2.18 2.69-6.38 5.04-11.68 5.04-11.09 0-21.25-9.66-21.25-22.09 0-12.52 10.16-22.26 21.25-22.26 5.29 0 9.49 2.35 11.68 4.96h.34v-3.61h9.25zm-8.56 20.92c0-7.81-5.21-13.52-11.84-13.52-6.72 0-12.35 5.71-12.35 13.52 0 7.73 5.63 13.36 12.35 13.36 6.63 0 11.84-5.63 11.84-13.36z"/>
            <path fill="#34A853" d="M225 3v65h-9.5V3h9.5z"/>
            <path fill="#EA4335" d="M262.02 54.48l7.56 5.04c-2.44 3.61-8.32 9.83-18.48 9.83-12.6 0-22.01-9.74-22.01-22.18 0-13.19 9.49-22.18 20.92-22.18 11.51 0 17.14 9.16 18.98 14.11l1.01 2.52-29.65 12.28c2.27 4.45 5.8 6.72 10.75 6.72 4.96 0 8.4-2.44 10.92-6.14zm-23.27-7.98l19.82-8.23c-1.09-2.77-4.37-4.7-8.23-4.7-4.95 0-11.84 4.37-11.59 12.93z"/>
            <path fill="#4285F4" d="M35.29 41.41V32h31.tattoo c.16 1.01.24 2.07.24 3.19C67 47.35 59.05 58 44.87 58c-12.86 0-23.27-10.41-23.27-23.27s10.41-23.27 23.27-23.27c6.46 0 11.6 2.52 15.67 6.42l-6.63 6.63c-2.52-2.36-5.88-3.78-9.04-3.78-7.56 0-13.61 6.21-13.61 14.03s6.05 14.03 13.61 14.03c8.65 0 11.84-6.05 12.44-9.38H35.29v-.0z"/>
        </svg>
    </div>

    <!-- ═══════════════════════════════════════════
         STEP 1 – Enter Email
    ════════════════════════════════════════════ -->
    <div class="step active" id="step1">
        <h1 class="g-title">Sign in</h1>
        <p class="g-subtitle">Use your Google Account</p>

        <div class="g-input-wrap" id="emailWrap">
            <input type="email" class="g-input" id="emailInput" placeholder=" " autocomplete="email" spellcheck="false">
            <label class="g-label" for="emailInput">Email or phone</label>
        </div>
        <p class="g-error-msg" id="emailError">Enter a valid email address</p>
        <p class="g-helper">Not your computer? Use a Private browsing window to sign in. <a href="https://support.google.com/chrome/answer/95464" target="_blank" class="g-link" style="font-size:12px;">Learn more</a></p>

        <div class="g-actions">
            <a href="https://accounts.google.com/signup" target="_blank" class="g-link">Create account</a>
            <button class="g-btn-next" id="nextBtn1" onclick="goToStep2()">Next</button>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════
         STEP 2 – Enter Password
    ════════════════════════════════════════════ -->
    <div class="step" id="step2">
        <!-- Avatar (initials) -->
        <div class="g-avatar" id="userAvatar">?</div>

        <!-- Email chip -->
        <div style="text-align:center; margin-bottom:24px;">
            <div class="g-email-chip" id="emailChip" style="display:inline-flex;">
                <!-- person icon -->
                <svg width="14" height="14" viewBox="0 0 24 24" fill="#5f6368">
                    <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
                </svg>
                <span id="chipEmail">user@gmail.com</span>
                <!-- dropdown arrow -->
                <svg width="10" height="10" viewBox="0 0 24 24" fill="#5f6368" style="margin-left:2px;">
                    <path d="M7 10l5 5 5-5z"/>
                </svg>
            </div>
        </div>

        <h1 class="g-title">Welcome</h1>
        <p class="g-subtitle" style="margin-bottom:24px;" id="welcomeSub">Enter your password to continue</p>

        <div class="g-input-wrap" id="passWrap">
            <input type="password" class="g-input" id="passInput" placeholder=" " autocomplete="current-password">
            <label class="g-label" for="passInput">Enter your password</label>
            <span class="g-pass-toggle" id="passToggle" title="Show password">&#128065;</span>
        </div>
        <p class="g-error-msg" id="passError">Wrong password. Try again or click "Forgot password" to reset it.</p>

        <div style="margin-bottom:4px;">
            <a href="forgot_password.php" class="g-link" style="font-size:14px;">Forgot password?</a>
        </div>

        <div class="g-actions">
            <button class="g-btn-next" style="background:#fff; color:#1a73e8; border:1px solid #dadce0; box-shadow:none;" onclick="backToStep1()">Back</button>
            <button class="g-btn-next" id="signInBtn" onclick="doSignIn()">Sign in</button>
        </div>
    </div>

</div>

<!-- Footer -->
<div class="g-footer">
    <div>
        <select style="border:none;background:transparent;font-size:12px;color:#5f6368;cursor:pointer;font-family:inherit;">
            <option>English (United Kingdom)</option>
        </select>
    </div>
    <div>
        <a href="#">Help</a>
        <a href="#">Privacy</a>
        <a href="#">Terms</a>
    </div>
</div>

<div class="g-back-notice">
    <a href="login.php">← Back to Project Rock Ascent login</a>
</div>

<!-- Hidden form that POSTs to our callback -->
<form id="authForm" action="social_callback.php" method="POST" style="display:none;">
    <input type="hidden" name="provider" value="google">
    <input type="hidden" name="g_email" id="formEmail">
</form>

<script>
    // ── Helpers ──────────────────────────────────────
    const $ = id => document.getElementById(id);

    function showSpinner() {
        $('spinnerOverlay').classList.add('show');
    }

    function initials(email) {
        const name = email.split('@')[0];
        return name.charAt(0).toUpperCase();
    }

    // ── Step 1 → Step 2 ──────────────────────────────
    function goToStep2() {
        const email = $('emailInput').value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!email || !emailRegex.test(email)) {
            $('emailWrap').classList.add('error');
            $('emailError').style.display = 'block';
            $('emailInput').focus();
            return;
        }

        $('emailWrap').classList.remove('error');
        $('emailError').style.display = 'none';

        // Populate step 2
        $('chipEmail').textContent = email;
        $('userAvatar').textContent = initials(email);
        $('welcomeSub').textContent = 'Hi ' + email.split('@')[0].replace(/[^a-zA-Z ]/g, '') + '!';

        showSpinner();
        setTimeout(() => {
            $('spinnerOverlay').classList.remove('show');
            $('step1').classList.remove('active');
            $('step2').classList.add('active');
            $('passInput').focus();
        }, 900);
    }

    // ── Step 2 → Step 1 ──────────────────────────────
    function backToStep1() {
        $('passInput').value = '';
        $('passError').style.display = 'none';
        $('passWrap').classList.remove('error');
        $('step2').classList.remove('active');
        $('step1').classList.add('active');
        $('emailInput').focus();
    }

    // ── Sign In (submit to PHP) ───────────────────────
    function doSignIn() {
        const email = $('emailInput').value.trim();
        const pass  = $('passInput').value;

        if (!pass) {
            $('passWrap').classList.add('error');
            $('passError').style.display = 'block';
            $('passInput').focus();
            return;
        }

        $('passWrap').classList.remove('error');
        $('passError').style.display = 'none';

        // Set form values and submit
        $('formEmail').value = email;
        
        showSpinner();
        // Submit after brief Google-like loading delay
        setTimeout(() => {
            $('authForm').submit();
        }, 1200);
    }

    // ── Password show/hide ────────────────────────────
    $('passToggle').addEventListener('click', function() {
        const inp = $('passInput');
        if (inp.type === 'password') {
            inp.type = 'text';
            this.innerHTML = '&#128064;'; // eye open
        } else {
            inp.type = 'password';
            this.innerHTML = '&#128065;'; // eye with line
        }
    });

    // ── Allow Enter key on fields ─────────────────────
    $('emailInput').addEventListener('keydown', e => { if (e.key === 'Enter') goToStep2(); });
    $('passInput').addEventListener('keydown',  e => { if (e.key === 'Enter') doSignIn(); });

    // ── Click email chip to go back ───────────────────
    $('emailChip').addEventListener('click', backToStep1);
</script>
</body>
</html>
