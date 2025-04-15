<?php
session_start();
include 'config.php';

$userName = 'Guest';
$userRole = 'guest';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $query = mysqli_query($conn, "SELECT name, user_type FROM user_form WHERE id = '$user_id'") or die('User fetch failed');
    if (mysqli_num_rows($query) > 0) {
        $userData = mysqli_fetch_assoc($query);
        $userName = $userData['name'];
        $userRole = $userData['user_type'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>wst project</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="crt.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark w-100">
        <div class="container-fluid justify-content-center">
            <ul class="navbar-nav text-center">
                <li class="nav-item mx-2">
                    <a class="nav-link active" href="index.php">Home</a>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link" href="products.php">Products</a>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link" href="profile.php">Profile</a>
                </li>
            </ul>
        </div>
    </nav>

    <div id="crt-boot-overlay"></div>

    <div id="monitor">
        <div id="crt">
            <div class="scanline"></div>
            <div class="terminal text-glitch" id="terminal"></div>
        </div>
        <div class="crt-corners">
            <div class="corner top-left">B01651145 - Corey Black</div>
            <div class="corner top-right" id="clock">--/--/----, --:--:--</div>
            <div class="corner bottom-left">COMP09023<br>Web Server Technologies</div>
            <div class="corner bottom-right">
                1.0.0<br>
                <a class="source-link" href="https://dev.to/ekeijl/retro-crt-terminal-screen-in-css-js-4afh"
                    target="_blank" rel="noopener noreferrer">source &lt;</a>
            </div>
        </div>
    </div>

    <audio id="boot-sound" src="/audio/boot.mp3" preload="auto"></audio>
<script>
    const user_id = <?= json_encode($_SESSION['user_id'] ?? null) ?>;
</script>
    <script>
        const userName = <?= json_encode($userName); ?>;
        const userRole = <?= json_encode($userRole); ?>;
    </script>
    <script src="scripts/terminal.js"></script>
</body>


</html>