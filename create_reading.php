<?php
require_once "config/session_timeout.php";
include "config/auth.php";
include "config/db.php";
include "includes/csrf_token.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    verify_csrf_token($_POST["csrf_token"] ?? "");

    $user_id = $_SESSION["user_id"];

    $area = trim($_POST["area"] ?? "");
    $reading_date = $_POST["reading_date"] ?? "";
    $am1 = $_POST["am1"] ?? "";
    $am2 = $_POST["am2"] ?? "";
    $pm1 = $_POST["pm1"] ?? "";
    $pm2 = $_POST["pm2"] ?? "";

    if (empty($area) || empty($reading_date) || $am1 === "" || $am2 === "" || $pm1 === "" || $pm2 === "") {
        $message = "<div class='alert error'>All rainfall fields are required.</div>";
    } elseif (!is_numeric($am1) || !is_numeric($am2) || !is_numeric($pm1) || !is_numeric($pm2) || $am1 < 0 || $am2 < 0 || $pm1 < 0 || $pm2 < 0) {
        $message = "<div class='alert error'>Rainfall values must be positive numbers.</div>";
    } else {

        $stmt = $conn->prepare("
            INSERT INTO rainfall_data
            (user_id, area, reading_date, am1, am2, pm1, pm2)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param("issdddd", $user_id, $area, $reading_date, $am1, $am2, $pm1, $pm2);

        if ($stmt->execute()) {
            $message = "<div class='alert success'>Rainfall reading added successfully!</div>";
        } else {
            $message = "<div class='alert error'>Error adding rainfall reading.</div>";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Rainfall Reading - CEA Rainfall Portal</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
*{margin:0;padding:0;box-sizing:border-box;}

body{
    font-family:'Poppins',sans-serif;
    background:linear-gradient(to bottom,#eef6fb,#f4f8fb);
    color:#123;
}

a{text-decoration:none;}

.navbar{
    width:100%;
    background:linear-gradient(to right,#063b5c,#0b6b8f);
    padding:18px 48px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    box-shadow:0 6px 20px rgba(0,0,0,0.15);
}

.logo{
    color:white;
    font-size:1.8rem;
    font-weight:800;
}

.logo-icon{margin-right:10px;}

.nav-links{
    display:flex;
    gap:14px;
    align-items:center;
    flex-wrap:wrap;
}

.nav-links a{
    color:white;
    font-weight:600;
    padding:10px 14px;
    border-radius:10px;
    transition:0.25s;
}

.nav-links a:hover{
    background:rgba(255,255,255,0.15);
}

.page-wrapper{
    min-height:calc(100vh - 100px);
    display:flex;
    align-items:center;
    justify-content:center;
    padding:38px 20px 60px;
}

.form-layout{
    width:100%;
    max-width:1180px;
    display:grid;
    grid-template-columns:1fr 1.08fr;
    background:white;
    border-radius:24px;
    overflow:hidden;
    box-shadow:0 18px 48px rgba(0,0,0,0.10);
}

.form-left{
    background:
        linear-gradient(rgba(3,37,57,0.88), rgba(3,37,57,0.62)),
        url("https://images.unsplash.com/photo-1501691223387-dd0500403074?auto=format&fit=crop&w=1200&q=80");
    background-size:cover;
    background-position:center;
    color:white;
    padding:60px 46px;
    display:flex;
    flex-direction:column;
    justify-content:center;
}

.form-left h1{
    font-size:2.45rem;
    line-height:1.15;
    margin-bottom:18px;
}

.form-left p{
    font-size:1rem;
    line-height:1.8;
    color:#e8f7ff;
    max-width:430px;
}

.form-left ul{
    margin-top:24px;
    padding-left:18px;
    color:#e8f7ff;
    line-height:1.9;
}

.form-right{
    padding:40px 36px;
}

.form-card h2{
    font-size:2rem;
    margin-bottom:8px;
    color:#063b5c;
}

.subtext{
    color:#52616b;
    margin-bottom:22px;
    font-size:0.96rem;
}

.alert{
    padding:13px 15px;
    border-radius:12px;
    margin-bottom:18px;
    font-size:0.95rem;
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

.form-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:16px;
}

.form-group{
    margin-bottom:16px;
}

.form-group.full{
    grid-column:1 / -1;
}

label{
    display:block;
    margin-bottom:8px;
    font-weight:600;
    color:#063b5c;
    font-size:0.93rem;
}

input,
select{
    width:100%;
    padding:13px 14px;
    border-radius:12px;
    border:1px solid #cbd5e1;
    font-size:0.95rem;
    outline:none;
    transition:0.25s ease;
    background:#fff;
    font-family:'Poppins',sans-serif;
}

input:focus,
select:focus{
    border-color:#38bdf8;
    box-shadow:0 0 0 3px rgba(56,189,248,0.18);
}

button{
    width:100%;
    padding:15px;
    border:none;
    border-radius:12px;
    background:#38bdf8;
    color:#063b5c;
    font-size:1rem;
    font-weight:800;
    cursor:pointer;
    transition:0.25s ease;
    margin-top:4px;
}

button:hover{
    background:#0ea5e9;
}

.bottom-link{
    text-align:center;
    margin-top:18px;
    font-size:0.94rem;
    color:#52616b;
}

.bottom-link a{
    color:#0369a1;
    font-weight:700;
}

.bottom-link a:hover{
    text-decoration:underline;
}

@media (max-width: 980px){
    .form-layout{
        grid-template-columns:1fr;
    }

    .form-left{
        min-height:260px;
        padding:42px 28px;
    }

    .form-right{
        padding:34px 24px;
    }
}

@media (max-width: 640px){
    .navbar{
        flex-direction:column;
        gap:14px;
        text-align:center;
        padding:18px 20px;
    }

    .nav-links{
        justify-content:center;
    }

    .form-grid{
        grid-template-columns:1fr;
    }

    .form-left h1{
        font-size:2rem;
    }
}
</style>
</head>

<body>

<nav class="navbar">
    <div class="logo">
        <span class="logo-icon">🌧️</span> CEA Rainfall Portal
    </div>

    <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="index.php#api">API Endpoints</a>
        <a href="view_readings.php">Rainfall Data</a>
        <a href="create_reading.php">New Reading</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<div class="page-wrapper">
    <div class="form-layout">

        <div class="form-left">
            <h1>Create Rainfall Reading</h1>

            <p>
                Submit rainfall measurements for a selected Centrala sensor location,
                including AM1, AM2, PM1 and PM2 readings in millimetres.
            </p>

            <ul>
                <li>Select one of the 18 Centrala rainfall sensor locations</li>
                <li>Enter rainfall values for AM1, AM2, PM1 and PM2</li>
                <li>All rainfall readings are measured in millimetres (mm)</li>
            </ul>
        </div>

        <div class="form-right">
            <div class="form-card">
                <h2>New Rainfall Reading</h2>

                <p class="subtext">
                    Complete the form below to add rainfall data to the CEA rainfall database.
                </p>

                <?php echo $message; ?>

                <form method="POST">

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?php echo csrf_token(); ?>"
                    >

                    <div class="form-grid">

                        <div class="form-group">
                            <label for="area">Location / Area</label>
                            <select name="area" id="area" required>
                                <option value="">Select a location</option>
                                <option value="Erean">Erean</option>
                                <option value="Brunad">Brunad</option>
                                <option value="Bylyn">Bylyn</option>
                                <option value="Docia">Docia</option>
                                <option value="Marend">Marend</option>
                                <option value="Pryn">Pryn</option>
                                <option value="Zord">Zord</option>
                                <option value="Yaean">Yaean</option>
                                <option value="Frestin">Frestin</option>
                                <option value="Stonyam">Stonyam</option>
                                <option value="Ryall">Ryall</option>
                                <option value="Ruril">Ruril</option>
                                <option value="Keivia">Keivia</option>
                                <option value="Tallan">Tallan</option>
                                <option value="Adohad">Adohad</option>
                                <option value="Obelyn">Obelyn</option>
                                <option value="Holmer">Holmer</option>
                                <option value="Vertwall">Vertwall</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="reading_date">Reading Date</label>
                            <input type="date" name="reading_date" id="reading_date" required>
                        </div>

                        <div class="form-group">
                            <label for="am1">AM1 Rainfall (mm)</label>
                            <input type="number" step="0.1" min="0" name="am1" id="am1" required>
                        </div>

                        <div class="form-group">
                            <label for="am2">AM2 Rainfall (mm)</label>
                            <input type="number" step="0.1" min="0" name="am2" id="am2" required>
                        </div>

                        <div class="form-group">
                            <label for="pm1">PM1 Rainfall (mm)</label>
                            <input type="number" step="0.1" min="0" name="pm1" id="pm1" required>
                        </div>

                        <div class="form-group">
                            <label for="pm2">PM2 Rainfall (mm)</label>
                            <input type="number" step="0.1" min="0" name="pm2" id="pm2" required>
                        </div>

                        <div class="form-group full">
                            <button type="submit">Add Rainfall Reading</button>
                        </div>

                    </div>
                </form>

                <div class="bottom-link">
                    Want to review rainfall readings?
                    <a href="view_readings.php">View rainfall data</a>
                </div>

            </div>
        </div>

    </div>
</div>

</body>
</html>