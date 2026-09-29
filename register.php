<?php
session_start();
require_once "Config/db.php";

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    // Basic validation
    if (empty($name) || empty($email) || empty($phone) || empty($password)) {

        $message = "Please fill all fields.";
        $message_type = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $message_type = "error";

    } elseif (!preg_match("/^[0-9]{10}$/", $phone)) {

        $message = "Phone number must contain 10 digits.";
        $message_type = "error";

    } elseif (strlen($password) < 6) {

        $message = "Password must be at least 6 characters.";
        $message_type = "error";

    } elseif ($password !== $confirm_password) {

        $message = "Passwords do not match.";
        $message_type = "error";

    } else {

        // Check whether email already exists
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $message = "An account with this email already exists.";
            $message_type = "error";

        } else {

            // Generate account number
            $account_number = "AC" . rand(10000000, 99999999);

            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert user
            $stmt = $conn->prepare(
                "INSERT INTO users 
                (account_number, name, email, phone, password) 
                VALUES (?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "sssss",
                $account_number,
                $name,
                $email,
                $phone,
                $hashed_password
            );

            if ($stmt->execute()) {

                $_SESSION["success_message"] =
                    "Account created successfully! Your Account Number is " . $account_number;

                header("Location: login.php");
                exit();

            } else {

                $message = "Something went wrong. Please try again.";
                $message_type = "error";
            }

            $stmt->close();
        }

        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Create Account - Bank Management System</title>

    <link rel="stylesheet" href="Css/style.css">

</head>

<body class="auth-page">

<div class="register-container">

    <!-- LEFT SECTION -->
    <div class="register-left">

        <div class="bank-symbol">
            ◆
        </div>

        <h1>Bank Management System</h1>

        <h2>
            Banking made<br>
            <span>simple & secure.</span>
        </h2>

        <p>
            Create your account and manage your finances
            securely from one convenient platform.
        </p>

        <div class="security-item">
            <div class="security-icon">🔒</div>
            <div>
                <strong>Secure Banking</strong>
                <small>Your information is protected.</small>
            </div>
        </div>

        <div class="security-item">
            <div class="security-icon">⚡</div>
            <div>
                <strong>Fast Transactions</strong>
                <small>Manage transactions quickly.</small>
            </div>
        </div>

        <div class="security-item">
            <div class="security-icon">🛡️</div>
            <div>
                <strong>Protected Account</strong>
                <small>Safe and secure account access.</small>
            </div>
        </div>

    </div>


    <!-- RIGHT SECTION -->
    <div class="register-right">

        <div class="form-header">

            <span>CREATE ACCOUNT</span>

            <h1>Open your account</h1>

            <p>
                Enter your details to get started.
            </p>

        </div>


        <?php if (!empty($message)): ?>

            <div class="form-message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php endif; ?>


        <form method="POST" action="">

            <!-- NAME -->
            <div class="form-group">

                <label>Full Name</label>

                <input
                    type="text"
                    name="name"
                    placeholder="Enter your full name"
                    required
                >

            </div>


            <!-- EMAIL -->
            <div class="form-group">

                <label>Email Address</label>

                <input
                    type="email"
                    name="email"
                    placeholder="Enter your email"
                    required
                >

            </div>


            <!-- PHONE -->
            <div class="form-group">

                <label>Phone Number</label>

                <input
                    type="tel"
                    name="phone"
                    placeholder="Enter 10 digit phone number"
                    maxlength="10"
                    required
                >

            </div>


            <!-- PASSWORD -->
            <div class="form-group">

                <label>Password</label>

                <input
                    type="password"
                    name="password"
                    placeholder="Minimum 6 characters"
                    minlength="6"
                    required
                >

            </div>


            <!-- CONFIRM PASSWORD -->
            <div class="form-group">

                <label>Confirm Password</label>

                <input
                    type="password"
                    name="confirm_password"
                    placeholder="Re-enter your password"
                    minlength="6"
                    required
                >

            </div>


            <!-- BUTTON -->
            <button type="submit" class="create-account-btn">
                Create Account
            </button>

        </form>


        <div class="login-link">

            Already have an account?

            <a href="login.php">
                Login
            </a>

        </div>

    </div>

</div>


<script src="js/script.js"></script>

</body>
</html>