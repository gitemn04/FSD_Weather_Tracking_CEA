<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CEA Rainfall Tracking Portal</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

html{
    scroll-behavior:smooth;
}

body{
    font-family:'Poppins', sans-serif;
    background:#f4f8fb;
    color:#123;
}

a{
    text-decoration:none;
}

.navbar{
    width:100%;
    background:linear-gradient(to right,#063b5c,#0b6b8f);
    padding:18px 48px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    box-shadow:0 6px 20px rgba(0,0,0,0.15);
    position:sticky;
    top:0;
    z-index:1000;
}

.logo{
    color:white;
    font-size:1.8rem;
    font-weight:800;
}

.logo span{
    margin-right:10px;
}

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

.nav-btn{
    background:#38bdf8;
    color:#063b5c !important;
    font-weight:800 !important;
}

/* Subtle styling distinction for privileged administrator modules */
.admin-link {
    border: 1px dashed rgba(56, 189, 248, 0.4);
}

.admin-link:hover {
    background: rgba(56, 189, 248, 0.15) !important;
}

.logout-btn{
    background:#ef4444;
    color:white !important;
    font-weight:800 !important;
}

.logout-btn:hover{
    background:#dc2626 !important;
}

.hero{
    min-height:82vh;
    display:flex;
    align-items:center;
    background:
        linear-gradient(to right, rgba(3,37,57,0.88), rgba(3,37,57,0.35)),
        url("https://images.unsplash.com/photo-1501691223387-dd0500403074?auto=format&fit=crop&w=1600&q=80");
    background-size:cover;
    background-position:center;
    color:white;
}

.hero-content{
    width:min(90%, 1200px);
    margin:0 auto;
    max-width:720px;
}

.hero h1{
    font-size:4rem;
    line-height:1.1;
    margin-bottom:22px;
    font-weight:800;
}

.hero p{
    font-size:1.15rem;
    line-height:1.8;
    margin-bottom:30px;
    color:#e8f7ff;
}

.hero-buttons{
    display:flex;
    gap:16px;
    flex-wrap:wrap;
}

.btn{
    display:inline-block;
    padding:14px 26px;
    border-radius:12px;
    font-weight:700;
    transition:0.25s;
}

.btn-primary{
    background:#38bdf8;
    color:#063b5c;
}

.btn-secondary{
    background:white;
    color:#063b5c;
}

.btn:hover{
    transform:translateY(-3px);
}

.cards-section{
    margin-top:-55px;
    position:relative;
    z-index:5;
}

.container{
    width:min(90%, 1200px);
    margin:0 auto;
}

.cards-grid{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:24px;
}

.card{
    background:white;
    padding:32px;
    border-radius:22px;
    box-shadow:0 14px 35px rgba(0,0,0,0.10);
    text-align:center;
    min-height:230px;
}

.card-icon{
    width:70px;
    height:70px;
    border-radius:50%;
    background:#e0f2fe;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:2rem;
    margin:0 auto 18px;
}

.card h3{
    color:#063b5c;
    font-size:1.35rem;
    margin-bottom:12px;
}

.card p{
    color:#52616b;
    line-height:1.7;
    font-size:0.96rem;
}

.section{
    padding:70px 0;
}

.section h2{
    font-size:2.3rem;
    color:#063b5c;
    margin-bottom:20px;
    text-align:center;
}

.section-intro{
    max-width:760px;
    margin:0 auto 38px;
    text-align:center;
    color:#52616b;
    line-height:1.8;
}

.api-grid{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:20px;
}

.api-card{
    background:white;
    border-radius:18px;
    padding:24px;
    box-shadow:0 10px 25px rgba(0,0,0,0.07);
}

.api-card h3{
    color:#075985;
    margin-bottom:10px;
}

.api-card code{
    display:block;
    background:#eff6ff;
    padding:14px;
    border-radius:10px;
    color:#0f172a;
    overflow-x:auto;
    font-size:0.9rem;
}

.stats{
    background:#e0f2fe;
    padding:50px 0;
}

.stats-grid{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:20px;
    text-align:center;
}

.stat-box{
    background:white;
    padding:28px 18px;
    border-radius:18px;
    box-shadow:0 8px 22px rgba(0,0,0,0.06);
}

.stat-box h3{
    font-size:2rem;
    color:#0369a1;
}

.stat-box p{
    color:#475569;
    font-weight:600;
}

.footer{
    background:#063b5c;
    color:white;
    padding:38px 0 18px;
}

.footer-grid{
    display:grid;
    grid-template-columns:1.5fr 1fr 1fr;
    gap:30px;
}

.footer h3{
    margin-bottom:12px;
}

.footer p,
.footer a,
.footer li{
    color:#dbeafe;
    line-height:1.8;
    font-size:0.94rem;
}

.footer ul{
    list-style:none;
}

.footer-bottom{
    text-align:center;
    margin-top:28px;
    padding-top:14px;
    border-top:1px solid rgba(255,255,255,0.18);
    color:#bfdbfe;
}

@media(max-width:900px){
    .navbar{
        flex-direction:column;
        gap:16px;
        text-align:center;
    }

    .hero h1{
        font-size:2.8rem;
    }

    .cards-grid,
    .api-grid,
    .stats-grid,
    .footer-grid{
        grid-template-columns:1fr;
    }

    .cards-section{
        margin-top:30px;
    }
}
</style>
</head>

<body>

<nav class="navbar">
    <div class="logo">
        <span>🌧️</span> CEA Rainfall Portal
    </div>

    <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="#api">API Endpoints</a>
        <a href="view_readings.php">Rainfall Data</a>

        <?php if(isset($_SESSION["user_id"])): ?>
            <a href="account.php">My API Key</a>
            
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="admin_logs.php" class="admin-link">Admin Logs</a>
                <a href="api_docs.php" class="admin-link">API Docs</a>
            <?php endif; ?>

            <a href="logout.php" class="logout-btn">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php" class="nav-btn">Get API Key</a>
        <?php endif; ?>
    </div>
</nav>

<section class="hero">
    <div class="hero-content">
        <h1>Rainfall Data Access for Centrala</h1>

        <p>
            The Centrala Environmental Agency provides a web-based rainfall data portal
            and API for universities, researchers, and climate specialists studying rainfall
            patterns across Centrala and its suburban regions.
        </p>

        <div class="hero-buttons">
            <a href="register.php" class="btn btn-primary">Register for Free API Key</a>
            <a href="view_readings.php" class="btn btn-secondary">View Rainfall Data</a>
        </div>
    </div>
</section>

<section class="cards-section">
    <div class="container">
        <div class="cards-grid">

            <div class="card">
                <div class="card-icon">📍</div>
                <h3>18 Sensor Locations</h3>
                <p>
                    Rainfall is collected from 18 geographical locations including Erean,
                    Brunad, Zord, Tallan, Ryall, Holmer and Vertwall.
                </p>
            </div>

            <div class="card">
                <div class="card-icon">⏱️</div>
                <h3>Four Readings Per Day</h3>
                <p>
                    Each sensor station records rainfall every six hours using AM1, AM2,
                    PM1 and PM2 readings measured in millimetres.
                </p>
            </div>

            <div class="card">
                <div class="card-icon">🔑</div>
                <h3>API Key Access</h3>
                <p>
                    Registered users receive a free API key. All API requests are validated
                    and logged for monitoring and archive purposes.
                </p>
            </div>

        </div>
    </div>
</section>

<section class="section" id="api">
    <div class="container">
        <h2>Required API Endpoints</h2>

        <p class="section-intro">
            The CEA rainfall system provides documented API endpoints for accessing
            rainfall readings by day, by location, by day and location, or across all
            available records.
        </p>

        <div class="api-grid">

            <div class="api-card">
                <h3>All Rainfall Data</h3>
                <code>/api/rainfall_all.php?api_key=YOUR_KEY</code>
            </div>

            <div class="api-card">
                <h3>Rainfall by Day</h3>
                <code>/api/rainfall_by_day.php?api_key=YOUR_KEY&date=2023-10-01</code>
            </div>

            <div class="api-card">
                <h3>Rainfall by Location</h3>
                <code>/api/rainfall_by_area.php?api_key=YOUR_KEY&area=Erean</code>
            </div>

            <div class="api-card">
                <h3>Rainfall by Day and Location</h3>
                <code>/api/rainfall_by_day_area.php?api_key=YOUR_KEY&date=2023-10-01&area=Erean</code>
            </div>

        </div>
    </div>
</section>

<section class="stats">
    <div class="container">
        <div class="stats-grid">

            <div class="stat-box">
                <h3>18</h3>
                <p>Rainfall Locations</p>
            </div>

            <div class="stat-box">
                <h3>4</h3>
                <p>Readings Per Day</p>
            </div>

            <div class="stat-box">
                <h3>1dp</h3>
                <p>Millimetre Accuracy</p>
            </div>

            <div class="stat-box">
                <h3>API</h3>
                <p>Research Data Access</p>
            </div>

        </div>
    </div>
</section>

<footer class="footer">
    <div class="container">
        <div class="footer-grid">

            <div>
                <h3>About CEA</h3>
                <p>
                    The Centrala Environmental Agency monitors rainfall data across the
                    municipality to support climate research, environmental planning and
                    public data access.
                </p>
            </div>

            <div>
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="register.php">Get API Key</a></li>
                    <li><a href="view_readings.php">Rainfall Data</a></li>
                    <li><a href="#api">API Endpoints</a></li>
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