<?php

session_start();

require_once "config/db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    // Basic validation
    if (empty($email) || empty($password)) {

        $error = "Please enter your email and password.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } else {

        // Find user by email
        $stmt = $conn->prepare(
            "SELECT id, account_number, name, email, password, balance
             FROM users
             WHERE email = ?
             LIMIT 1"
        );

        $stmt->bind_param("s", $email);

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $user["password"])) {

                // Create session
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["account_number"] = $user["account_number"];
                $_SESSION["user_name"] = $user["name"];
                $_SESSION["user_email"] = $user["email"];

                // Redirect to dashboard
                header("Location: dashboard.php");
                exit;

            } else {

                $error = "Incorrect email or password.";

            }

        } else {

            $error = "Incorrect email or password.";

        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Login - BankFlow</title>

    <link rel="stylesheet" href="css/style.css">

    <style>

        .login-page {
            min-height: calc(100vh - 75px);

            display: flex;
            align-items: center;
            justify-content: center;

            padding: 50px 20px;

            background:
                linear-gradient(
                    135deg,
                    #eef5ff,
                    #ffffff
                );
        }

        .login-container {
            width: 100%;
            max-width: 450px;

            background: #ffffff;

            padding: 40px;

            border-radius: 20px;

            box-shadow:
                0 20px 50px rgba(0, 0, 0, 0.10);
        }

        .login-header {
            text-align: center;

            margin-bottom: 30px;
        }

        .login-icon {
            width: 65px;
            height: 65px;

            margin: 0 auto 15px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 50%;

            background: #e1edff;

            font-size: 30px;
        }

        .login-header h1 {
            margin-bottom: 8px;

            font-size: 30px;

            color: #172033;
        }

        .login-header p {
            color: #667085;

            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;

            margin-bottom: 8px;

            font-size: 14px;

            font-weight: 600;

            color: #344054;
        }

        .form-group input {
            width: 100%;

            padding: 13px 15px;

            border: 1px solid #d0d5dd;

            border-radius: 9px;

            font-size: 15px;

            outline: none;

            transition: 0.2s;
        }

        .form-group input:focus {
            border-color: #1769e0;

            box-shadow:
                0 0 0 3px rgba(23, 105, 224, 0.10);
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper input {
            padding-right: 50px;
        }

        .show-password {
            position: absolute;

            right: 15px;
            top: 50%;

            transform: translateY(-50%);

            border: none;

            background: transparent;

            cursor: pointer;

            font-size: 18px;
        }

        .error-message {
            background: #fff1f2;

            color: #be123c;

            border: 1px solid #fecdd3;

            padding: 12px 15px;

            border-radius: 8px;

            margin-bottom: 20px;

            font-size: 14px;
        }

        .login-submit {
            width: 100%;

            border: none;

            cursor: pointer;

            font-size: 16px;

            padding: 13px;

            margin-top: 5px;
        }

        .login-footer {
            text-align: center;

            margin-top: 25px;

            color: #667085;

            font-size: 14px;
        }

        .login-footer a {
            color: #1769e0;

            font-weight: 600;
        }

        .security-note {
            text-align: center;

            margin-top: 20px;

            font-size: 12px;

            color: #98a2b3;
        }

    </style>

</head>

<body>

<header class="navbar">

    <div class="logo">
        <span>◆</span> BANK<span>FLOW</span>
    </div>

    <nav>

        <a href="index.php">
            Home
        </a>

        <a href="index.php#services">
            Services
        </a>

        <a href="index.php#security">
            Security
        </a>

    </nav>

    <div class="nav-buttons">

        <a href="register.php" class="primary-btn">
            Open Account
        </a>

    </div>

</header>


<main class="login-page">

    <div class="login-container">

        <div class="login-header">

            <div class="login-icon">
                🔐
            </div>

            <h1>
                Welcome Back
            </h1>

            <p>
                Login to your BankFlow account
            </p>

        </div>


        <?php if (!empty($error)): ?>

            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>


        <form method="POST"
              action=""
              id="loginForm">

            <div class="form-group">

                <label for="email">
                    Email Address
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Enter your email"
                    autocomplete="email"
                    required
                >

            </div>


            <div class="form-group">

                <label for="password">
                    Password
                </label>

                <div class="password-wrapper">

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                    >

                    <button
                        type="button"
                        class="show-password"
                        onclick="togglePassword()"
                        aria-label="Show password"
                    >
                        👁
                    </button>

                </div>

            </div>


            <button
                type="submit"
                class="primary-btn login-submit"
            >
                Login to Account
            </button>

        </form>


        <div class="login-footer">

            Don't have an account?

            <a href="register.php">
                Create Account
            </a>

        </div>


        <div class="security-note">

            🔒 Your login information is securely protected.

        </div>

    </div>

</main>


<footer class="footer">

    <div class="logo">
        <span>◆</span> BANK<span>FLOW</span>
    </div>

    <p>
        © 2026 BankFlow. All rights reserved.
    </p>

</footer>


<script>

function togglePassword() {

    const password =
        document.getElementById("password");

    const button =
        document.querySelector(".show-password");

    if (password.type === "password") {

        password.type = "text";

        button.textContent = "🙈";

    } else {

        password.type = "password";

        button.textContent = "👁";

    }

}

</script>

<script src="js/script.js"></script>

</body>

</html>