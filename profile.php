
<?php
session_start();

require_once "Config/db.php";

// Check whether the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = (int) $_SESSION['user_id'];

$sql = "SELECT id, account_number, name, email, phone, balance, created_at
        FROM users
        WHERE id = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database query error.");
}

$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    die("Profile not found. Please check your account.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | Bank Management System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            margin: 0;
            padding: 30px 15px;
            color: #1e293b;
        }

        .profile-card {
            max-width: 650px;
            margin: 30px auto;
            background: white;
            padding: 30px;
            border-radius: 14px;
            box-shadow: 0 5px 20px rgba(0,0,0,.08);
        }

        h1 {
            color: #123b68;
            margin-top: 0;
        }

        .detail {
            padding: 14px 0;
            border-bottom: 1px solid #e2e8f0;
            overflow-wrap: anywhere;
        }

        .label {
            font-weight: bold;
            display: inline-block;
            width: 165px;
        }

        .back {
            display: inline-block;
            margin-top: 25px;
            padding: 11px 18px;
            background: #123b68;
            color: white;
            text-decoration: none;
            border-radius: 7px;
        }

        .back:hover {
            background: #0b2949;
        }

        @media (max-width: 480px) {
            .label {
                display: block;
                width: auto;
                margin-bottom: 6px;
            }
        }
    </style>
    <link rel="stylesheet" href="css/profile.css">
</head>
<body>

<div class="profile-card">
    <h1>My Profile</h1>
    <p>Your registered account details</p>

    <div class="detail">
        <span class="label">Customer ID:</span>
        <?= htmlspecialchars((string)$user['id']) ?>
    </div>

    <div class="detail">
        <span class="label">Account Number:</span>
        <?= htmlspecialchars((string)$user['account_number']) ?>
    </div>

    <div class="detail">
        <span class="label">Full Name:</span>
        <?= htmlspecialchars($user['name']) ?>
    </div>

    <div class="detail">
        <span class="label">Email:</span>
        <?= htmlspecialchars($user['email']) ?>
    </div>

    <div class="detail">
        <span class="label">Phone:</span>
        <?= htmlspecialchars($user['phone']) ?>
    </div>

    <div class="detail">
        <span class="label">Account Balance:</span>
        ₹<?= number_format((float)$user['balance'], 2) ?>
    </div>

    <div class="detail">
        <span class="label">Registered On:</span>
        <?= htmlspecialchars((string)$user['created_at']) ?>
    </div>

    <a class="back" href="dashboard.php">Back to Dashboard</a>
</div>
<script src="js/profile.js"></script>
</body>
</html>
