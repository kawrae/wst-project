<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Secure Cart Project</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
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
                    <a class="nav-link" href="products.php">Products</a>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link" href="profile.php">Profile</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Hero -->
    <section class="hero-section position-relative ">
        <canvas id="bg-canvas"></canvas>
        <div class="hero-content">
            <h1>Secure Shopping Made Simple</h1>
            <p>Welcome to B01651145 project submission for Web Server Technologies COMP09023</p>
            <a href="products.php" class="btn btn-main">Start Exploring</a>
        </div>
    </section>


    <!-- Features -->
    <section class="features">
        <div class="container">
            <div class="row justify-content-center g-4">
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="card p-4 text-center">
                        <i class="fas fa-lock fa-2x mb-3 text-success"></i>
                        <h5>Security</h5>
                        <p>Sessions, SQL injection prevention, user verification & more.</p>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="card p-4 text-center">
                        <i class="fas fa-user-circle fa-2x mb-3 text-success"></i>
                        <h5>Login System</h5>
                        <p>Robust authentication and account management.</p>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="card p-4 text-center">
                        <i class="fas fa-shopping-cart fa-2x mb-3 text-success"></i>
                        <h5>Cart Features</h5>
                        <p>AJAX-based cart, real-time updates, total calculation.</p>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
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