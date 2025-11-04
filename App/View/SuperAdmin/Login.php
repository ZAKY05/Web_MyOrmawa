<!DOCTYPE html>
<html lang="en">
<?php
session_start();
?>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>MyOrmawa - Login</title>

    <!-- Custom fonts -->
    <link href="../../Template/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom styles -->
    <link href="../../Template/css/sb-admin-2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../Asset//Css/Login.css">
</head>

<body>
    <a href="../LandingPage/Home.php" class="back-button">
        <i class="fas fa-arrow-left"></i> Back
    </a>

    <div class="login-container">
        <div class="login-card">
            <div class="login-image">
                <img src="../../../Asset/Img/MyO3-removebg-preview.png" alt="MyOrmawa Logo">
            </div>

            <div class="login-form-section">
                <div class="logo-section">
                    <h1>Welcome Back!</h1>
                    <p>Please login to continue to MyOrmawa</p>
                </div>



                <form action="../../../Function/LoginFunction.php" method="POST" id="loginForm">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                        </div>
                    </div>

                    <button type="submit" name="login" class="btn-login" id="loginBtn">
                        <span>Login to MyOrmawa</span>
                    </button>
                </form>

                <div class="divider">
                    <span>Or</span>
                </div>

                <div class="links">
                    <a href="forgot-password.html">
                        <i class="fas fa-key"></i> Forgot Password?
                    </a>
                    <br>
                    <a href="register.html">
                        <i class="fas fa-user-plus"></i> Create an Account
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Notifikasi -->
    <div class="modal fade" id="loginErrorModal" tabindex="-1" aria-labelledby="loginErrorLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-danger shadow-lg">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="loginErrorLabel">
                        <i class="fas fa-exclamation-triangle"></i> Login Gagal
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <?php if (isset($_SESSION['login_error'])) : ?>
                        <p class="fw-semibold"><?php echo $_SESSION['login_error']; ?></p>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>



    <!-- JS -->
    <script src="../../Template/vendor/jquery/jquery.min.js"></script>
    <script src="../../Template/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../Template/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="../../Template/js/sb-admin-2.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            <?php if (isset($_SESSION['login_error'])) : ?>
                const modal = new bootstrap.Modal(document.getElementById('loginErrorModal'));
                modal.show();
                <?php unset($_SESSION['login_error']); ?>
            <?php endif; ?>
        });
        // Form submission animation
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('loginBtn');
            btn.classList.add('loading');
            btn.querySelector('span').textContent = 'Logging in...';
        });

        // Input focus animation
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.parentElement.style.transform = 'scale(1.02)';
                this.parentElement.parentElement.style.transition = 'transform 0.3s ease';
            });

            input.addEventListener('blur', function() {
                this.parentElement.parentElement.style.transform = 'scale(1)';
            });
        });
    </script>
</body>

</html>