<?php
session_start();
include_once __DIR__ . '/../includes/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    if ($username == '' || $email == '' || $password == '') {

        $error = "All fields are required.";

    } else {

        $sql = "SELECT id FROM users WHERE username='$username' OR email='$email' LIMIT 1";
        $check = mysqli_query($mysqli, $sql);

        if ($check && mysqli_num_rows($check) > 0) {

            $error = "User already exists.";

        } else {

            $insert = "INSERT INTO users (username,email,password,created_at)
                       VALUES ('$username','$email','$password',NOW())";

            if (mysqli_query($mysqli, $insert)) {

                echo "<script>
                alert('Account Created Successfully!');
                window.location='login.php';
                </script>";
                exit;

            } else {
                $error = "Database error.";
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Admin Sign Up</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
:root{--accent1:#7b4df4;--accent2:#c44ed8;--muted:#7b7f89;}
*{box-sizing:border-box}
body{margin:0;font-family:"Segoe UI",Roboto,Arial;
background:linear-gradient(135deg,var(--accent1),var(--accent2));}

.page{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:40px;}
.card{border:5px solid #c44ed8;
  width:520px;background:#fff;border-radius:22px;
box-shadow:0 18px 50px rgba(10,10,30,0.18);padding:36px 44px;}

h1{text-align:center;margin:0 0 8px;color:var(--accent1)}

p{text-align:center;color:var(--muted)}
.field{margin-bottom:16px}
label{font-weight:600}
input{width:100%;padding:12px;border-radius:10px; border:1px solid #eef2f7}
.btn{width:100%;padding:14px;border:0;border-radius:14px;
background:linear-gradient(90deg,var(--accent1),var(--accent2));
color:#fff;font-weight:700;cursor:pointer}
.err{background:#fff0f0;color:#b00020;padding:10px;border-radius:10px;margin-bottom:10px}
.ok{background:#f0fff4;color:#1b7a2a;padding:10px;border-radius:10px;margin-bottom:10px}
.helper{text-align:center;margin-top:10px}
.helper a{color:var(--accent1);text-decoration:none}
</style>
</head>
<body>
<div class="page">
<div class="card">
<h1>Create Admin</h1>
<p>Register a new admin account</p>

<?php if($error): ?><div class="err"><?=htmlspecialchars($error)?></div><?php endif;?>
<?php if($success): ?><div class="ok"><?=htmlspecialchars($success)?></div><?php endif;?>


 <form method="post" action="" novalidate>
        <div class="field">
          <label for="username">Username</label>
          <input id="username" name="username" type="text" placeholder="Enter your username" autocomplete="username"  required/>
        </div>

        <div class="field">
     <label for="email">Email</label>
    <input type="email" name="email" type="text" placeholder="Enter your email" autocomplete="email" required>
  </div>


        <div class="field">
          <label for="password">Password</label>
          <input id="password" name="password" type="password" placeholder="Enter your password" autocomplete="current-password" />
        </div>

        <button class="btn" type="submit">Create Account</button>
      </form>
<div class="helper">
  Already have an account?
  <a href="login.php"> Login</a>
</div>
</div>
</div>
</body>
</html>
