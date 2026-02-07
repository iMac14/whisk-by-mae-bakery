<?php
session_start();
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $error = "Email already registered";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, address, password, user_type) VALUES (?, ?, ?, ?, ?, 'customer')");
        if ($stmt->execute([$name, $email, $phone, $address, $password])) {
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['user_type'] = 'customer';
            $_SESSION['user_name'] = $name;
            header('Location: ../customer/home.php');
            exit();
        } else {
            $error = "Registration failed";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Whisk by Mae</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background-color: #FFF8DC;
            color: #000000;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            width: 100%;
            max-width: 900px;
            background-color: transparent;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .logo-section {
            background: #FFF8DC;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .logo-section img {
            width: 100%;
            height: auto;
        }
        .form-section {
            padding: 3rem;
        }
        .form-control {
            background-color: #3d3d3d;
            border: 1px solid #555;
            color: #ffffff;
            border-radius: 8px;
        }
        .form-control:focus {
            background-color: #3d3d3d;
            border-color: #8B4513;
            color: #ffffff;
            box-shadow: 0 0 0 0.2rem rgba(139, 69, 19, 0.25);
        }
        .form-control::placeholder {
            color: #cccccc;
        }
        .btn-primary {
            background-color: #8B4513;
            border-color: #8B4513;
            border-radius: 8px;
            padding: 0.75rem 2rem;
            font-weight: 600;
        }
        .btn-primary:hover {
            background-color: #D2691E;
            border-color: #D2691E;
        }
        .text-muted {
            color: #cccccc !important;
        }
        a {
            color: #8B4513;
        }
        a:hover {
            color: #D2691E;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 d-flex justify-content-center">
                <div class="login-container">
                    <div class="row g-0">
                        <div class="col-md-6 form-section">
                            <h2 class="mb-4">Create Account</h2>
                            <p class="text-muted mb-4">Join us and discover delicious baked goods</p>
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger" role="alert">
                                    <?php echo $error; ?>
                                </div>
                            <?php endif; ?>
                            <form method="POST">
                                <div class="mb-3">
                                    <input type="text" class="form-control" name="name" placeholder="Full Name" required>
                                </div>
                                <div class="mb-3">
                                    <input type="email" class="form-control" name="email" placeholder="Email Address" required>
                                </div>
                                <div class="mb-3">
                                    <input type="tel" class="form-control" name="phone" placeholder="Phone Number" required>
                                </div>
                                <div class="mb-3">
                                    <textarea class="form-control" name="address" placeholder="Address" rows="3" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <input type="password" class="form-control" name="password" placeholder="Password" required>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Create Account</button>
                                </div>
                            </form>
                            <div class="text-center mt-4">
                                <p class="mb-0">Already have an account? <a href="login.php">Sign in</a></p>
                            </div>
                        </div>
                        <div class="col-md-6 logo-section">
                            <img src="../assets/images/logo-removebg-preview.png" alt="Whisk by Mae Logo">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
