<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Secure Cart Project</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: rgb(17, 46, 6);
            --secondary: rgb(130, 240, 163);
            --white: #fff;
            --dark: #1c1c1e;
        }

        * {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            outline: none;
            border: none;
            text-decoration: none;
        }

        body,
        html {
            height: 100%;
            margin: 0;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--white);
            scroll-behavior: smooth;
        }

        .hero-section {
            position: relative;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 2rem;
            overflow: hidden;
        }

        .hero-content {
            position: relative;
            z-index: 1;
            animation: slideInLeft 1.2s ease-out forwards;

            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .hero-section h1 {
            font-size: 3rem;
            font-weight: 700;
        }

        .hero-section p {
            font-size: 1.2rem;
            max-width: 600px;
        }

        .btn-main {
            background-color: var(--white);
            color: var(--dark);
            padding: 0.8rem 1.5rem;
            border-radius: 30px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-main:hover {
            background-color:rgb(204, 243, 191);
            transform: translateY(-1px);
            color: black;
        }

        .features {
            padding: 5rem 2rem;
            background: var(--white);
            color: var(--dark);
        }

        .features .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }

        .features .card:hover {
            transform: translateY(-5px);
        }

        canvas {
            position: absolute;
            top: 0;
            left: 0;
            z-index: 0;
            pointer-events: none;
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-100px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .hero-content {
            z-index: 1;
            animation: slideInLeft 1.2s ease-out forwards;
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark w-100">
        <div class="container-fluid justify-content-center">
            <ul class="navbar-nav text-center">
                <li class="nav-item mx-2">
                    <a class="nav-link active" href="index.php">Home</a>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link" href="profile.php">Profile</a>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link disabled" href="#">Contact</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Hero -->
    <section class="hero-section position-relative ">
        <canvas id="bg-canvas"></canvas>
        <div class="hero-content">
            <h1>Secure Shopping Made Simple</h1>
            <p>Welcome to B01651145 project submission for Web Server Technologies COMPO09023</p>
            <a href="profile.php" class="btn btn-main">Start Exploring</a>
        </div>
    </section>


    <!-- Features -->
    <section class="features">
        <div class="container">
            <h2 class="text-center mb-5">Project Highlights</h2>
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="card p-4 text-center">
                        <i class="fas fa-lock fa-2x mb-3 text-success"></i>
                        <h5>Security</h5>
                        <p>Sessions, SQL injection prevention, user verification & more.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-4 text-center">
                        <i class="fas fa-user-circle fa-2x mb-3 text-success"></i>
                        <h5>Login System</h5>
                        <p>Robust authentication and account management.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-4 text-center">
                        <i class="fas fa-shopping-cart fa-2x mb-3 text-success"></i>
                        <h5>Cart Features</h5>
                        <p>AJAX-based cart, real-time updates, total calculation.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-4 text-center">
                        <i class="fas fa-paint-brush fa-2x mb-3 text-success"></i>
                        <h5>UX Design</h5>
                        <p>Modern UI with responsive layouts and transitions.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Canvas Animation -->
    <script>
        const canvas = document.getElementById("bg-canvas");
        const ctx = canvas.getContext("2d");

        function resizeCanvas() {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        }

        resizeCanvas();
        window.addEventListener('resize', resizeCanvas);

        let stars = [];
        for (let i = 0; i < 100; i++) {
            stars.push({
                x: Math.random() * canvas.width,
                y: Math.random() * canvas.height,
                radius: Math.random() * 2,
                speed: Math.random() * 0.5 + 0.2
            });
        }

        function animate() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            for (let s of stars) {
                ctx.beginPath();
                ctx.arc(s.x, s.y, s.radius, 0, Math.PI * 2);
                ctx.fillStyle = "#ffffff88";
                ctx.fill();
                s.y += s.speed;
                if (s.y > canvas.height) s.y = 0;
            }
            requestAnimationFrame(animate);
        }

        animate();
    </script>
</body>

</html>