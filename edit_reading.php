<?php
require_once "config/session_timeout.php";
include "config/db.php";
include "includes/csrf_token.php";

// Let the session timeout script handle initialization smoothly
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Global Authentication Gate
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$message = "";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Invalid rainfall reading selected.";
    exit();
}

$id = (int) $_GET['id'];
$user_id = $_SESSION["user_id"];
$user_role = $_SESSION["role"] ?? "user"; // Fallback default

// Fetch the targeted record details
$stmt = $conn->prepare("SELECT * FROM rainfall_data WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Rainfall reading not found.";
    exit();
}

$row = $result->fetch_assoc();

// RBAC Authorization Gate: Admins can manage everything, users are bound to ownership
if ($user_role !== "admin" && $row["user_id"] != $user_id) {
    echo "Access denied. You do not have permission to alter this environmental reading resource.";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    verify_csrf_token($_POST["csrf_token"] ?? "");

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

        // Construct context-aware query depending on security role privileges
        if ($user_role === "admin") {
            $update = $conn->prepare("
                UPDATE rainfall_data
                SET area = ?, reading_date = ?, am1 = ?, am2 = ?, pm1 = ?, pm2 = ?
                WHERE id = ?
            ");
            $update->bind_param("ssddddi", $area, $reading_date, $am1, $am2, $pm1, $pm2, $id);
        } else {
            $update = $conn->prepare("
                UPDATE rainfall_data
                SET area = ?, reading_date = ?, am1 = ?, am2 = ?, pm1 = ?, pm2 = ?
                WHERE id = ? AND user_id = ?
            ");
            $update->bind_param("ssddddii", $area, $reading_date, $am1, $am2, $pm1, $pm2, $id, $user_id);
        }

        if ($update->execute()) {
            $message = "<div class='alert success'>Rainfall reading updated successfully.</div>";

            // Pull refreshed view dataset 
            $refresh = $conn->prepare("SELECT * FROM rainfall_data WHERE id = ?");
            $refresh->bind_param("i", $id);
            $refresh->execute();
            $row = $refresh->get_result()->fetch_assoc();
        } else {
            $message = "<div class='alert error'>Error updating rainfall reading.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Rainfall Reading - CEA Rainfall Portal</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
*{margin:0;padding:0;box-sizing:border-box;}

body{
    font-family:'Poppins',sans-serif;
    background:linear-gradient(to bottom,#eef6fb,#f4f8fb);
    color:#123;
}

a{text-decoration:none;}

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

@media (max-width: 980px){
    .form-layout{grid-template-columns:1fr;}
    .form-left{min-height:260px;padding:42px 28px;}
    .form-right{padding:34px 24px;}
}

@media (max-width: 640px){
    .form-grid{grid-template-columns:1fr;}
    .form-left h1{font-size:2rem;}
}
</style>
</head>

<body>

<?php include "navbar.php"; ?>

<div class="page-wrapper">
    <div class="form-layout">

        <div class="form-left">
            <h1>Edit Rainfall Reading</h1>
            <p>
                Update rainfall measurements for a selected Centrala sensor location,
                including AM1, AM2, PM1 and PM2 readings in millimetres.
            </p>

            <ul>
                <li>Modify the existing rainfall location or reading date</li>
                <li>Update AM1, AM2, PM1 and PM2 rainfall values</li>
                <li>All rainfall values must be positive numbers</li>
            </ul>
        </div>

        <div class="form-right">
            <div class="form-card">
                <h2>Update Rainfall Reading</h2>

                <p class="subtext">
                    Edit the selected rainfall record and save the updated measurement data.
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
                            <select id="area" name="area" required>
                                <?php
                                $areas = ["Erean","Brunad","Bylyn","Docia","Marend","Pryn","Zord","Yaean","Frestin","Stonyam","Ryall","Ruril","Keivia","Tallan","Adohad","Obelyn","Holmer","Vertwall"];
                                foreach ($areas as $areaOption) {
                                    $selected = ($row["area"] === $areaOption) ? "selected" : "";
                                    echo "<option value='$areaOption' $selected>$areaOption</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="reading_date">Reading Date</label>
                            <input type="date" id="reading_date" name="reading_date" value="<?php echo htmlspecialchars($row['reading_date']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="am1">AM1 Rainfall (mm)</label>
                            <input type="number" step="0.1" min="0" id="am1" name="am1" value="<?php echo htmlspecialchars($row['am1']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="am2">AM2 Rainfall (mm)</label>
                            <input type="number" step="0.1" min="0" id="am2" name="am2" value="<?php echo htmlspecialchars($row['am2']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="pm1">PM1 Rainfall (mm)</label>
                            <input type="number" step="0.1" min="0" id="pm1" name="pm1" value="<?php echo htmlspecialchars($row['pm1']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="pm2">PM2 Rainfall (mm)</label>
                            <input type="number" step="0.1" min="0" id="pm2" name="pm2" value="<?php echo htmlspecialchars($row['pm2']); ?>" required>
                        </div>

                        <div class="form-group full">
                            <button type="submit">Update Rainfall Reading</button>
                        </div>

                    </div>
                </form>

                <div class="bottom-link">
                    Return to
                    <a href="view_readings.php">rainfall data records</a>
                </div>

            </div>
        </div>

    </div>
</div>

</body>
</html>