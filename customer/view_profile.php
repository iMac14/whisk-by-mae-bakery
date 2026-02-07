<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'customer') {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (!password_verify($current_password, $user['password'])) {
        $error = "Current password is incorrect";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match";
    } elseif (strlen($new_password) < 6) {
        $error = "New password must be at least 6 characters long";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        if ($stmt->execute([$hashed_password, $user_id])) {
            $success = "Password updated successfully";
        } else {
            $error = "Failed to update password";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Profile - Whisk by Mae</title>
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
        .profile-container {
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
            background: #FFF8DC;
            color: #000000;
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
        .user-info {
            background-color: #3d3d3d;
            color: #ffffff;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 d-flex justify-content-center">
                <div class="profile-container">
                    <div class="row g-0">
                        <div class="col-md-6 form-section">
                            <h2 class="mb-4">View Profile</h2>
                            <div class="user-info">
                                <h5><?php echo htmlspecialchars($user['name']); ?></h5>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                                <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
                                <p><strong>Address:</strong> <?php echo htmlspecialchars($user['address']); ?></p>
                            </div>

                            <h3 class="mb-4">Change Password</h3>
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger" role="alert">
                                    <?php echo $error; ?>
                                </div>
                            <?php endif; ?>
                            <?php if (isset($success)): ?>
                                <div class="alert alert-success" role="alert">
                                    <?php echo $success; ?>
                                </div>
                            <?php endif; ?>
                            <form method="POST">
                                <div class="mb-3">
                                    <input type="password" class="form-control" name="current_password" placeholder="Current Password" required>
                                </div>
                                <div class="mb-3">
                                    <input type="password" class="form-control" name="new_password" placeholder="New Password" required>
                                </div>
                                <div class="mb-3">
                                    <input type="password" class="form-control" name="confirm_password" placeholder="Confirm New Password" required>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Update Password</button>
                                </div>
                            </form>
                            <div class="text-center mt-4">
                                <p class="mb-0"><a href="home.php">Back to Home</a></p>
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
