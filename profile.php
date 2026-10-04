<?php
session_start();

require_once "config/db.php";

// Check whether user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Get logged-in user's details
$stmt = $conn->prepare(
    "SELECT account_number, name, email, phone, balance, created_at
     FROM users
     WHERE id = ?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 1) {

    $user = $result->fetch_assoc();

} else {

    echo "User profile not found.";
    exit();
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>My Profile - Bank Management System</title>

    <link rel="stylesheet" href="css/profile.css">

</head>

<body>

<div class="profile-page">

    <!-- HEADER -->

    <div class="profile-header">

        <div>
            <h1>My Profile</h1>
            <p>View your bank account information</p>
        </div>

        <a href="dashboard.php" class="back-btn">
            ← Dashboard
        </a>

    </div>


    <!-- PROFILE CARD -->

    <div class="profile-card">

        <!-- PROFILE ICON -->

        <div class="profile-icon">
            <?php echo strtoupper(substr($user["name"], 0, 1)); ?>
        </div>


        <h2>
            <?php echo htmlspecialchars($user["name"]); ?>
        </h2>

        <p class="account-number">
            Account No:
            <?php echo htmlspecialchars($user["account_number"]); ?>
        </p>


        <!-- ACCOUNT INFORMATION -->

        <div class="profile-details">

            <div class="detail-box">

                <span class="detail-label">
                    Full Name
                </span>

                <span class="detail-value">
                    <?php echo htmlspecialchars($user["name"]); ?>
                </span>

            </div>


            <div class="detail-box">

                <span class="detail-label">
                    Email Address
                </span>

                <span class="detail-value">
                    <?php echo htmlspecialchars($user["email"]); ?>
                </span>

            </div>


            <div class="detail-box">

                <span class="detail-label">
                    Phone Number
                </span>

                <span class="detail-value">
                    <?php echo htmlspecialchars($user["phone"]); ?>
                </span>

            </div>


            <div class="detail-box">

                <span class="detail-label">
                    Account Number
                </span>

                <span class="detail-value">
                    <?php echo htmlspecialchars($user["account_number"]); ?>
                </span>

            </div>


            <div class="detail-box">

                <span class="detail-label">
                    Current Balance
                </span>

                <span class="detail-value balance">
                    ₹<?php echo number_format($user["balance"], 2); ?>
                </span>

            </div>


            <div class="detail-box">

                <span class="detail-label">
                    Account Created
                </span>

                <span class="detail-value">
                    <?php echo htmlspecialchars($user["created_at"]); ?>
                </span>

            </div>

        </div>


        <!-- ACTIONS -->

        <div class="profile-actions">

            <a href="dashboard.php" class="dashboard-btn">
                Back to Dashboard
            </a>

            <a href="logout.php" class="logout-btn">
                Logout
            </a>

        </div>

    </div>

</div>

</body>
</html>