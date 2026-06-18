<?php
session_start();
include "config/db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Sanitize input case sensitivity to guarantee smooth user profile matching
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($username === "" || $password === "") {
        $message = "<div class='alert error'>Please fill in all fields.</div>";
    } else {

        $stmt = $conn->prepare("
            SELECT id, username, password_hash, role
            FROM users
            WHERE username = ?
        ");

        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user["password_hash"])) {

                unset($_SESSION["logout_message"]);

                // Set session variables firmly before shifting contexts
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["username"] = trim($user["username"]); // Clean database whitespace
                $_SESSION["role"] = $user["role"];

                // Write session close flags explicitly to ensure variable storage commits before redirection
                session_write_close();

                header("Location: view_readings.php");
                exit();

            } else {
                $message = "<div class='alert error'>Invalid username or password.</div>";
            }
        } else {
            $message = "<div class='alert error'>Invalid username or password.</div>";
        }

        $stmt->close();
    }
}
?>

<?php include "navbar.php"; ?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Login - CEA Rainfall Portal</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
body{
    margin:0;
    font-family:'Poppins',sans-serif;
    background:linear-gradient(to bottom,#eef6fb,#f4f8fb);
    color:#123;
}

.page-wrapper{
    min-height:calc(100vh - 90px);
    display:flex;
    align-items:center;
    justify-content:center;
    padding:50px 20px;
}

.login-layout{
    width:100%;
    max-width:1100px;
    display:grid;
    grid-template-columns:1fr 1fr;
    background:white;
    border-radius:24px;
    overflow:hidden;
    box-shadow:0 18px 48px rgba(0,0,0,0.10);
}

.login-left{
    background:
        linear-gradient(rgba(3,37,57,0.88), rgba(3,37,57,0.62)),
        url("https://images.unsplash.com/photo-1501691223387-dd0500403074?auto=format&fit=crop&w=1200&q=80");
    background-size:cover;
    background-position:center;
    color:white;
    padding:60px 45px;
    display:flex;
    flex-direction:column;
    justify-content:center;
}

.login-left h1{
    font-size:42px;
    line-height:1.15;
    margin-bottom:18px;
}

.login-left p{
    font-size:18px;
    line-height:1.8;
    color:#e8f7ff;
    max-width:430px;
}

.login-right{
    padding:50px 40px;
    display:flex;
    align-items:center;
    justify-content:center;
}

.form-card{
    width:100%;
    max-width:390px;
}

.form-card h2{
    margin-bottom:8px;
    font-size:32px;
    color:#063b5c;
}

.form-card .subtext{
    margin-bottom:24px;
    color:#52616b;
    font-size:15px;
}

.alert{
    padding:12px 14px;
    border-radius:10px;
    margin-bottom:18px;
    font-size:14px;
    font-weight:500;
}

.alert.error{
    background:#fee2e2;
    color:#991b1b;
    border:1px solid #fecaca;
}

.alert.success{
    background:#e0f2fe;
    color:#063b5c;
    border:1px solid #bae6fd;
}

.form-group{
    margin-bottom:16px;
}

label{
    display:block;
    margin-bottom:7px;
    font-weight:600;
    color:#063b5c;
    font-size:14px;
}

input{
    width:100%;
    padding:13px 14px;
    border-radius:10px;
    border:1px solid #cbd5e1;
    font-size:15px;
    outline:none;
    transition:0.25s ease;
    box-sizing:border-box;
    font-family:'Poppins',sans-serif;
}

input:focus{
    border-color:#38bdf8;
    box-shadow:0 0 0 3px rgba(56,189,248,0.18);
}

button{
    width:100%;
    padding:14px;
    border:none;
    border-radius:10px;
    background:#38bdf8;
    color:#063b5c;
    font-size:15px;
    font-weight:800;
    cursor:pointer;
    transition:0.25s ease;
    font-family:'Poppins',sans-serif;
}

button:hover{
    background:#0ea5e9;
}

.bottom-link{
    text-align:center;
    margin-top:18px;
    font-size:14px;
    color:#52616b;
}

.bottom-link a{
    color:#0369a1;
    text-decoration:none;
    font-weight:700;
}

.bottom-link a:hover{
    text-decoration:underline;
}

@media (max-width: 900px){
    .login-layout{
        grid-template-columns:1fr;
    }
    .login-left{
        min-height:260px;
        padding:40px 28px;
    }
    .login-left h1{
        font-size:34px;
    }
    .login-right{
        padding:35px 24px;
    }
}
</style>
</head>

<body>

<div class="page-wrapper">
    <div class="login-layout">

        <div class="login-left">
            <h1>Welcome Back to CEA Rainfall Portal</h1>
            <p>
                Sign in to manage rainfall readings, access API functionality,
                and support environmental monitoring across Centrala.
            </p>
        </div>

        <div class="login-right">
            <div class="form-card">

                <h2>Login</h2>
                <p class="subtext">
                    Enter your username and password to continue.
                </p>

                <?php if (isset($_GET['timeout'])): ?>
                    <div class="alert error">
                        You were logged out because you were inactive for 30 minutes.
                    </div>
                <?php endif; ?>

                <?php
                if (!empty($message)) {
                    echo $message;
                } elseif (isset($_SESSION["logout_message"])) {
                    echo "<div class='alert success'>" . htmlspecialchars($_SESSION["logout_message"]) . "</div>";
                    unset($_SESSION["logout_message"]);
                }
                ?>

                <form action="login.php" method="POST">

                    <div class="form-group">
                        <label for="username">Username</label>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            placeholder="Enter your username"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Enter your password"
                            required
                        >
                    </div>

                    <button type="submit">Login</button>

                </form>

                <div class="bottom-link">
                    Don’t have an account?
                    <a href="register.php">Register here</a>
                </div>

            </div>
        </div>

    </div>
</div>

</body>
</html>