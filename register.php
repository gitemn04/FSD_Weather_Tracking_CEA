<?php
include "config/db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST["username"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    if ($username === "" || $email === "" || $password === "" || $confirm_password === "") {
        $message = "<div class='alert error'>Please fill in all required fields.</div>";
    } elseif (strlen($username) < 6) {
        $message = "<div class='alert error'>Username must be at least 6 characters.</div>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "<div class='alert error'>Please enter a valid email address.</div>";
    } elseif (strlen($password) < 8) {
        $message = "<div class='alert error'>Password must be at least 8 characters.</div>";
    } elseif ($password !== $confirm_password) {
        $message = "<div class='alert error'>Passwords do not match.</div>";
    } else {

        // Crucial fix: Added api_key to the SELECT query so we can retrieve it
        $check = $conn->prepare("
            SELECT id, api_key
            FROM users
            WHERE username = ? OR email = ?
        ");

        $check->bind_param("ss", $username, $email);
        $check->execute();
        $check_result = $check->get_result();

        if ($check_result && $check_result->num_rows > 0) {
            
            // Fetch the existing user data from the database match
            $existing_user = $check_result->fetch_assoc();
            $existing_api_key = $existing_user["api_key"];

            // Displaying the clean, enterprise-style notification with their token
            $message = "
            <div class='alert warning'>
                <strong>API KEY ALREADY GENERATED</strong><br><br>
                The API key was generated during registration. It will NOT be shown again on login.<br><br>
                <strong>User:</strong> " . htmlspecialchars($username) . "<br><br>
                • View / Copy your existing API key below:<br>
                <code>" . htmlspecialchars($existing_api_key) . "</code><br><br>
                • Use this API key to access endpoints.
            </div>
            ";

        } else {

            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $api_key = bin2hex(random_bytes(16));

            $stmt = $conn->prepare("
                INSERT INTO users
                (username, email, password_hash, api_key)
                VALUES (?, ?, ?, ?)
            ");

            $stmt->bind_param("ssss", $username, $email, $password_hash, $api_key);

            if ($stmt->execute()) {
                $message = "
                    <div class='alert success'>
                        <strong>Registration successful!</strong><br><br>
                        Your free API key is:<br>
                        <code>$api_key</code><br><br>
                        Save this API key for accessing rainfall API endpoints.
                    </div>
                ";
            } else {
                $message = "<div class='alert error'>Error creating account. Please try again.</div>";
            }

            $stmt->close();
        }

        $check->close();
    }
}
?>

<?php include "navbar.php"; ?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Register - CEA Rainfall Portal</title>

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

.register-layout{
    width:100%;
    max-width:1100px;
    display:grid;
    grid-template-columns:1fr 1fr;
    background:white;
    border-radius:24px;
    overflow:hidden;
    box-shadow:0 18px 48px rgba(0,0,0,0.10);
}

.register-left{
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

.register-left h1{
    font-size:42px;
    line-height:1.15;
    margin-bottom:18px;
}

.register-left p{
    font-size:18px;
    line-height:1.8;
    color:#e8f7ff;
    max-width:430px;
}

.register-left ul{
    margin-top:24px;
    padding-left:18px;
    color:#e8f7ff;
    line-height:1.9;
}

.register-right{
    padding:50px 40px;
    display:flex;
    align-items:center;
    justify-content:center;
}

.form-card{
    width:100%;
    max-width:410px;
}

.form-card h2{
    margin-bottom:8px;
    font-size:32px;
    color:#063b5c;
}

.subtext{
    margin-bottom:24px;
    color:#52616b;
    font-size:15px;
    line-height:1.7;
}

.alert{
    padding:14px;
    margin-bottom:20px;
    border-radius:10px;
    text-align:center;
    line-height:1.6;
    font-size:14px;
    font-weight:500;
}

.alert.success{
    background:#e0f2fe;
    color:#063b5c;
    border:1px solid #bae6fd;
}

.alert.warning {
    background:#fff7ed;
    color:#c2410c;
    border:1px solid #fed7aa;
}

.alert.success code, .alert.warning code{
    display:block;
    background:white;
    color:#0f172a;
    padding:10px;
    border-radius:8px;
    margin-top:8px;
    font-size:13px;
    word-break:break-all;
    border: 1px solid #e2e8f0;
}

.alert.error{
    background:#fee2e2;
    color:#991b1b;
    border:1px solid #fecaca;
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

@media(max-width:900px){
    .register-layout{
        grid-template-columns:1fr;
    }

    .register-left{
        min-height:260px;
        padding:40px 28px;
    }

    .register-left h1{
        font-size:34px;
    }

    .register-right{
        padding:35px 24px;
    }
}
</style>
</head>

<body>

<div class="page-wrapper">
    <div class="register-layout">

        <div class="register-left">
            <h1>Register for CEA API Access</h1>

            <p>
                Create an account to receive a free API key for accessing
                Centrala rainfall data and REST-style API services.
            </p>

            <ul>
                <li>Generate a secure personal API key</li>
                <li>Access rainfall readings by day, area, or both</li>
                <li>Support research and environmental monitoring</li>
            </ul>
        </div>

        <div class="register-right">
            <div class="form-card">

                <h2>Create Account</h2>

                <p class="subtext">
                    Complete the form below to register and receive your API key.
                </p>

                <?php echo $message; ?>

                <form method="POST">

                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" required>
                    </div>

                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" required>
                    </div>

                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" name="confirm_password" required>
                    </div>

                    <button type="submit">Register and Generate API Key</button>

                </form>

                <div class="bottom-link">
                    Already have an account?
                    <a href="login.php">Login here</a>
                </div>

            </div>
        </div>

    </div>
</div>

</body>
</html>