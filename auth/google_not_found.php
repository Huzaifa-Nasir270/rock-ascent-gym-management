<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account not found – Project Rock Ascent</title>
    <link rel="icon" href="https://www.google.com/favicon.ico">
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
        .g-card {
            background: #fff;
            border-radius: 28px;
            box-shadow: 0 2px 10px rgba(0,0,0,.12), 0 6px 30px rgba(0,0,0,.06);
            max-width: 450px;
            width: 100%;
            padding: 48px 40px 36px;
            text-align: center;
        }
        .g-logo { margin-bottom: 28px; }
        .g-logo svg { height: 24px; }
        .g-icon {
            width: 64px; height: 64px;
            background: #fce8e6;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 28px;
            margin: 0 auto 20px;
        }
        h1 { font-size: 22px; font-weight: 400; color: #202124; margin-bottom: 12px; }
        p  { font-size: 14px; color: #5f6368; line-height: 1.6; margin-bottom: 8px; }
        .email-highlight {
            display: inline-block;
            background: #f1f3f4;
            border-radius: 4px;
            padding: 2px 8px;
            font-weight: 500;
            color: #202124;
            font-size: 14px;
            word-break: break-all;
        }
        .actions {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 32px;
        }
        .btn {
            padding: 10px 24px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            font-family: inherit;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background .2s, box-shadow .2s;
            letter-spacing: .25px;
        }
        .btn-primary {
            background: #1a73e8;
            color: white;
            border: none;
        }
        .btn-primary:hover { background: #1765cc; box-shadow: 0 1px 3px rgba(0,0,0,.3); }
        .btn-outline {
            background: white;
            color: #1a73e8;
            border: 1px solid #dadce0;
        }
        .btn-outline:hover { background: #f1f3f4; }
        .g-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 24px;
            font-size: 12px;
            color: #5f6368;
            max-width: 450px;
            width: 100%;
        }
        .g-footer a { color: #5f6368; text-decoration: none; margin: 0 6px; }
        .g-footer a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<?php
session_start();
$email = $_SESSION['google_email_not_found'] ?? '';
unset($_SESSION['google_email_not_found']);
$safeEmail = htmlspecialchars($email);
?>

<div class="g-card">
    <!-- Google Logo -->
    <div class="g-logo">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 272 92">
            <path fill="#EA4335" d="M115.75 47.18c0 12.77-9.99 22.18-22.25 22.18s-22.25-9.41-22.25-22.18C71.25 34.32 81.24 25 93.5 25s22.25 9.32 22.25 22.18zm-9.74 0c0-7.98-5.79-13.44-12.51-13.44S80.99 39.2 80.99 47.18c0 7.9 5.79 13.44 12.51 13.44s12.51-5.55 12.51-13.44z"/>
            <path fill="#FBBC05" d="M163.75 47.18c0 12.77-9.99 22.18-22.25 22.18s-22.25-9.41-22.25-22.18c0-12.85 9.99-22.18 22.25-22.18s22.25 9.32 22.25 22.18zm-9.74 0c0-7.98-5.79-13.44-12.51-13.44s-12.51 5.46-12.51 13.44c0 7.9 5.79 13.44 12.51 13.44s12.51-5.55 12.51-13.44z"/>
            <path fill="#4285F4" d="M209.75 26.34v39.82c0 16.38-9.66 23.07-21.08 23.07-10.75 0-17.22-7.19-19.66-13.07l8.48-3.53c1.51 3.61 5.21 7.87 11.17 7.87 7.31 0 11.84-4.51 11.84-13v-3.19h-.34c-2.18 2.69-6.38 5.04-11.68 5.04-11.09 0-21.25-9.66-21.25-22.09 0-12.52 10.16-22.26 21.25-22.26 5.29 0 9.49 2.35 11.68 4.96h.34v-3.61h9.25zm-8.56 20.92c0-7.81-5.21-13.52-11.84-13.52-6.72 0-12.35 5.71-12.35 13.52 0 7.73 5.63 13.36 12.35 13.36 6.63 0 11.84-5.63 11.84-13.36z"/>
            <path fill="#34A853" d="M225 3v65h-9.5V3h9.5z"/>
            <path fill="#EA4335" d="M262.02 54.48l7.56 5.04c-2.44 3.61-8.32 9.83-18.48 9.83-12.6 0-22.01-9.74-22.01-22.18 0-13.19 9.49-22.18 20.92-22.18 11.51 0 17.14 9.16 18.98 14.11l1.01 2.52-29.65 12.28c2.27 4.45 5.8 6.72 10.75 6.72 4.96 0 8.4-2.44 10.92-6.14zm-23.27-7.98l19.82-8.23c-1.09-2.77-4.37-4.7-8.23-4.7-4.95 0-11.84 4.37-11.59 12.93z"/>
        </svg>
    </div>

    <div class="g-icon">🔍</div>

    <h1>Couldn't find your Google Account</h1>

    <?php if ($safeEmail): ?>
        <p>The email address <span class="email-highlight"><?php echo $safeEmail; ?></span> is not registered with Project Rock Ascent.</p>
    <?php else: ?>
        <p>No matching account was found in our system.</p>
    <?php endif; ?>

    <p style="margin-top:8px;">You can create a new account or try a different email.</p>

    <div class="actions">
        <a href="signup.php<?php echo $safeEmail ? '?email=' . urlencode($email) : ''; ?>" class="btn btn-primary">
            Create account
        </a>
        <a href="social_login.php?provider=google" class="btn btn-outline">
            Try a different email
        </a>
        <a href="login.php" class="btn btn-outline" style="color:#5f6368; border-color:#dadce0;">
            Back to login
        </a>
    </div>
</div>

<div class="g-footer">
    <div>
        <select style="border:none;background:transparent;font-size:12px;color:#5f6368;font-family:inherit;">
            <option>English (United Kingdom)</option>
        </select>
    </div>
    <div>
        <a href="#">Help</a>
        <a href="#">Privacy</a>
        <a href="#">Terms</a>
    </div>
</div>

</body>
</html>
