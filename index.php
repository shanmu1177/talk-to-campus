<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Talk To Campus</title>
  <style>
    /* Color Theme (Matches Your Admin Login UI) */
    :root {
        --bg1:#7b4df4;     /* Purple */
        --bg2:#d44fd8;     /* Pinkish Purple */
        --white:#ffffff;
        --muted:#666;
    }

    /* FULL SCREEN GRADIENT BACKGROUND */
    body {
        margin:0;
        font-family:"Segoe UI", Roboto, Arial, sans-serif;
        background:linear-gradient(135deg, var(--bg1), var(--bg2));
        min-height:100vh;
        display:flex;
        align-items:center;
        justify-content:center;
    }

    /* WHITE HERO CONTAINER */
    .hero {
        width:100%;
        max-width:900px;
        text-align:center;
        padding:60px 30px;
        border-radius:22px;
        background:#ffffff; /* Pure white box */
        color:#333;
        box-shadow:0 18px 40px rgba(0,0,0,0.18);
    }

    /* LOGO BACKGROUND PURPLE GRADIENT */
    .logo {
        width:84px;
        height:84px;
        margin:0 auto 18px;
        background:linear-gradient(135deg, var(--bg1), var(--bg2));
        border-radius:18px;
        display:flex;
        align-items:center;
        justify-content:center;
    }

    .logo i {
        font-size:40px;
        color:#fff;
        opacity:1;
    }

    /* TITLE PURPLE COLOUR */
    h1 {
        margin:6px 0 8px;
        font-size:46px;
        letter-spacing:0.5px;
        color:var(--bg1);
        font-weight:700;
    }

    /* SUBTEXT */
    p.lead {
        max-width:720px;
        margin:0 auto 28px;
        color:var(--muted);
        font-size:16px;
    }

    /* BUTTON → PURPLE GRADIENT */
    .btn {
        display:inline-block;
        padding:14px 34px;
        border-radius:36px;
        background:linear-gradient(90deg, var(--bg1), var(--bg2));
        color:#fff;
        font-weight:600;
        text-decoration:none;
        box-shadow:0 10px 25px rgba(120,60,200,0.25);
    }

    /* ADMIN LOGIN LINK COLOR PURPLE */
    .link {
        display:block;
        margin-top:18px;
        color:var(--bg1);
        text-decoration:none;
        cursor:pointer;
        font-weight:500;
    }

    @media (max-width:520px){
        h1{font-size:34px;}
        .hero{padding:36px 18px;}
    }
</style>

</head>
<body>
  <div class="hero">
    <div class="logo"><i>🤖</i></div>
    <h1>Talk To Campus</h1>
    <p class="lead">Your intelligent campus companion! Get instant answers to all college-related questions — admissions, 
        facilities, events and more.</p>

    <!-- Start Chatting goes to chat.php -->
    <a class="btn" href="chat.php">Start Chatting</a>
    <a class="link" href="admin/login.php">Admin Login →</a>
  </div>
</body>
</html>
