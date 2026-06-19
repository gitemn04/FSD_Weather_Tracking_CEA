<?php
require_once "config/session_timeout.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

include "config/db.php";
include "includes/csrf_token.php";

$search_value = "";
$result = null;

if (isset($_GET["search"]) && trim($_GET["search"]) !== "") {
    $search_value = trim($_GET["search"]);
    $search = "%" . $search_value . "%";

    $stmt = $conn->prepare("
        SELECT *
        FROM rainfall_data
        WHERE area LIKE ? OR reading_date LIKE ?
        ORDER BY reading_date DESC, id DESC
    ");
    $stmt->bind_param("ss", $search, $search);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("
        SELECT *
        FROM rainfall_data
        ORDER BY reading_date DESC, id DESC
    ");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>View Rainfall Data - CEA Rainfall Portal</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
*{margin:0;padding:0;box-sizing:border-box;}

body{
    font-family:'Poppins',sans-serif;
    background:#f4f8fb;
    color:#123;
}

a{text-decoration:none;}

.container{
    width:min(92%, 1280px);
    margin:0 auto;
}

.alert{
    padding:14px 16px;
    border-radius:14px;
    margin:0 0 22px;
    font-size:0.96rem;
    font-weight:600;
}

.alert.success{
    background:#e0f2fe;
    color:#063b5c;
    border:1px solid #bae6fd;
}

.page-hero{
    min-height:360px;
    display:flex;
    align-items:center;
    color:white;
    background:
        linear-gradient(to right, rgba(3,37,57,0.88), rgba(3,37,57,0.35)),
        url("https://images.unsplash.com/photo-1501691223387-dd0500403074?auto=format&fit=crop&w=1600&q=80");
    background-size:cover;
    background-position:center;
    margin-bottom:34px;
}

.page-hero-content{
    width:min(90%, 1200px);
    margin:0 auto;
    max-width:720px;
}

.page-hero-content h1{
    font-size:3.4rem;
    line-height:1.1;
    margin-bottom:18px;
    font-weight:800;
}

.page-hero-content p{
    font-size:1.08rem;
    line-height:1.8;
    color:#e8f7ff;
}

.search-section{margin-bottom:26px;}

.search-box{
    background:white;
    border-radius:18px;
    box-shadow:0 10px 25px rgba(0,0,0,0.07);
    padding:18px;
    display:flex;
    gap:12px;
    align-items:center;
    flex-wrap:wrap;
}

.search-box input{
    flex:1;
    min-width:260px;
    padding:14px 16px;
    border:1px solid #cbd5e1;
    border-radius:12px;
    font-size:0.98rem;
    outline:none;
    font-family:'Poppins',sans-serif;
}

.search-box button{
    padding:14px 22px;
    border:none;
    border-radius:12px;
    background:#38bdf8;
    color:#063b5c;
    font-size:0.98rem;
    font-weight:800;
    cursor:pointer;
    font-family:'Poppins',sans-serif;
}

.clear-link{
    color:#0369a1;
    font-weight:700;
    padding:10px 4px;
}

.section-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:14px;
    margin-bottom:18px;
}

.section-header h2{
    font-size:2rem;
    color:#063b5c;
}

.result-note{
    color:#52616b;
    font-size:0.94rem;
    font-weight:500;
}

.posts-grid{
    display:grid;
    grid-template-columns:repeat(3, 1fr);
    gap:24px;
    margin-bottom:50px;
}

.post-card{
    background:white;
    border-radius:20px;
    overflow:hidden;
    box-shadow:0 10px 25px rgba(0,0,0,0.07);
    transition:0.28s ease;
}

.post-card:hover{
    transform:translateY(-6px);
    box-shadow:0 18px 36px rgba(0,0,0,0.12);
}

.post-body{padding:22px;}

.post-top{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:12px;
    margin-bottom:16px;
}

.post-top h3{
    font-size:1.45rem;
    color:#063b5c;
}

.badge{
    background:#e0f2fe;
    color:#0369a1;
    border-radius:999px;
    padding:7px 11px;
    font-size:0.76rem;
    font-weight:700;
    white-space:nowrap;
}

.post-meta{
    display:grid;
    gap:10px;
    margin-bottom:14px;
}

.meta-item{
    color:#52616b;
    font-size:0.95rem;
    line-height:1.55;
}

.post-footer{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:10px;
    border-top:1px solid #e2e8f0;
    margin-top:18px;
    padding-top:14px;
    flex-wrap:wrap;
}

.author{
    color:#64748b;
    font-size:0.9rem;
}

.actions{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
}

.action-btn{
    display:inline-block;
    padding:9px 14px;
    border-radius:10px;
    font-size:0.87rem;
    font-weight:700;
    transition:0.25s ease;
}

.edit-btn{
    background:#e0f2fe;
    color:#0369a1;
}

.delete-btn{
    background:#fee2e2;
    color:#991b1b;
}

.empty-state{
    background:white;
    border-radius:22px;
    box-shadow:0 10px 25px rgba(0,0,0,0.07);
    padding:48px 28px;
    text-align:center;
    margin-bottom:50px;
}

.empty-state h3{
    font-size:1.6rem;
    color:#063b5c;
    margin-bottom:10px;
}

.empty-state p{
    color:#52616b;
    line-height:1.8;
    max-width:560px;
    margin:0 auto 18px;
}

.empty-state a{
    display:inline-block;
    background:#38bdf8;
    color:#063b5c;
    padding:13px 22px;
    border-radius:12px;
    font-weight:800;
}

.footer{
    background:#063b5c;
    color:white;
    margin-top:20px;
}

.footer-inner{padding:40px 0 18px;}

.footer-grid{
    display:grid;
    grid-template-columns:1.5fr 1fr 1.1fr;
    gap:30px;
}

.footer h3{
    font-size:1.18rem;
    margin-bottom:12px;
}

.footer p,
.footer li,
.footer a{
    color:#dbeafe;
    line-height:1.8;
    font-size:0.92rem;
}

.footer ul{list-style:none;}

.footer-bottom{
    text-align:center;
    color:#bfdbfe;
    border-top:1px solid rgba(255,255,255,0.18);
    margin-top:26px;
    padding-top:14px;
    font-size:0.84rem;
}

@media(max-width:1100px){
    .posts-grid,
    .footer-grid{
        grid-template-columns:repeat(2, 1fr);
    }
}

@media(max-width:900px){
    .posts-grid,
    .footer-grid{
        grid-template-columns:1fr;
    }

    .section-header{
        flex-direction:column;
        align-items:flex-start;
    }

    .page-hero-content h1{
        font-size:2.4rem;
    }
}
</style>
</head>

<body>

<?php include "navbar.php"; ?>

<section class="page-hero">
    <div class="page-hero-content">
        <h1>Rainfall Data Records</h1>
        <p>
            Browse rainfall measurements collected from Centrala sensor stations,
            including AM1, AM2, PM1 and PM2 readings across all recorded locations.
        </p>
    </div>
</section>

<div class="container">

<?php
if (isset($_SESSION["delete_message"])) {
    echo "<div class='alert success'>" . htmlspecialchars($_SESSION["delete_message"]) . "</div>";
    unset($_SESSION["delete_message"]);
}
?>

<section class="search-section">
    <form method="GET" class="search-box">
        <input
            type="text"
            name="search"
            placeholder="Search by area or date"
            value="<?php echo htmlspecialchars($search_value); ?>"
        >
        <button type="submit">Search</button>

        <?php if ($search_value !== ""): ?>
            <a class="clear-link" href="view_readings.php">Clear</a>
        <?php endif; ?>
    </form>
</section>

<div class="section-header">
    <h2>Rainfall Readings</h2>
    <div class="result-note">
        <?php echo ($search_value !== "") ? "Showing results for: " . htmlspecialchars($search_value) : "Showing all rainfall readings"; ?>
    </div>
</div>

<?php if ($result && $result->num_rows > 0): ?>
    <div class="posts-grid">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="post-card">
                <div class="post-body">

                    <div class="post-top">
                        <h3><?php echo htmlspecialchars($row["area"]); ?></h3>
                        <span class="badge">
                            <?php echo htmlspecialchars($row["reading_date"]); ?>
                        </span>
                    </div>

                    <div class="post-meta">
                        <div class="meta-item">🌧️ <strong>AM1:</strong> <?php echo htmlspecialchars($row["am1"]); ?> mm</div>
                        <div class="meta-item">🌧️ <strong>AM2:</strong> <?php echo htmlspecialchars($row["am2"]); ?> mm</div>
                        <div class="meta-item">🌧️ <strong>PM1:</strong> <?php echo htmlspecialchars($row["pm1"]); ?> mm</div>
                        <div class="meta-item">🌧️ <strong>PM2:</strong> <?php echo htmlspecialchars($row["pm2"]); ?> mm</div>
                    </div>

                    <div class="post-footer">
                        <div class="author">
                            Rainfall Monitoring Record
                        </div>

                        <div class="actions">
                            <a class="action-btn edit-btn" href="edit_reading.php?id=<?php echo $row["id"]; ?>">Edit</a>
                            <form action="delete_reading.php" method="POST" style="display:inline;" onsubmit="return confirm('Delete this rainfall reading?');">
                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?php echo csrf_token(); ?>"
                                >
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($row["id"]); ?>">
                                <button type="submit" class="action-btn delete-btn" style="border:none; cursor:pointer; font-family:'Poppins',sans-serif;">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <div class="empty-state">
        <h3>No rainfall readings found</h3>
        <p>
            There are currently no matching rainfall readings to display.
            Start by creating a new rainfall reading and your records will appear here.
        </p>
        <a href="create_reading.php">Create Rainfall Reading</a>
    </div>
<?php endif; ?>

</div>

<footer class="footer">
    <div class="container footer-inner">
        <div class="footer-grid">
            <div>
                <h3>About CEA</h3>
                <p>
                    The Centrala Environmental Agency monitors rainfall data across the municipality
                    to support climate research, environmental planning and public data access.
                </p>
            </div>

            <div>
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="register.php">Get API Key</a></li>
                    <li><a href="view_readings.php">Rainfall Data</a></li>
                    <li><a href="index.php#api">API Endpoints</a></li>
                </ul>
            </div>

            <div>
                <h3>Contact</h3>
                <p>✉ data@cea-centrala.org</p>
                <p>☎ +44 123 456 7890</p>
                <p>📍 Centrala Environmental Agency</p>
            </div>
        </div>

        <div class="footer-bottom">
            © 2026 CEA Rainfall Portal. Development server prototype.
        </div>
    </div>
</footer>

</body>
</html>