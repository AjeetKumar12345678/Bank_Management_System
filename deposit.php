
<?php
session_start();
require_once "Config/db.php";
require_once "Config/notifications.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION["user_id"];

if (empty($_SESSION["deposit_csrf"])) {
    $_SESSION["deposit_csrf"] = bin2hex(random_bytes(32));
}

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST["csrf_token"] ?? "";
    $amountInput = trim($_POST["amount"] ?? "");
    $description = trim($_POST["description"] ?? "");

    if (!hash_equals($_SESSION["deposit_csrf"], $token)) {
        $message = "Invalid request. Please refresh and try again.";
        $messageType = "error";
    } elseif (
        !preg_match('/^\d{1,8}(\.\d{1,2})?$/', $amountInput)
        || (float) $amountInput <= 0
    ) {
        $message = "Enter a valid deposit amount greater than zero.";
        $messageType = "error";
    } elseif ((float) $amountInput > 1000000) {
        $message = "The maximum deposit per transaction is ₹10,00,000.";
        $messageType = "error";
    } else {
        $amount = number_format((float) $amountInput, 2, ".", "");

        try {
            $conn->begin_transaction();

            // Lock the user's row while updating the balance.
            $stmt = $conn->prepare(
                "SELECT account_number
                 FROM users
                 WHERE id = ?
                 FOR UPDATE"
            );
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $account = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$account) {
                throw new Exception("Account not found.");
            }

            // Add the deposit to the account balance.
            $stmt = $conn->prepare(
                "UPDATE users
                 SET balance = balance + ?
                 WHERE id = ?"
            );
            $stmt->bind_param("di", $amount, $user_id);
            $stmt->execute();
            $stmt->close();

            // Record the deposit in transaction history.
            $type = "Deposit";
            $status = "Success";
            $details = $description !== ""
                ? substr($description, 0, 255)
                : "Cash deposit";

            $stmt = $conn->prepare(
                "INSERT INTO transactions
                 (account_number, transaction_type, amount,
                  description, status)
                 VALUES (?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "ssdss",
                $account["account_number"],
                $type,
                $amount,
                $details,
                $status
            );

            $stmt->execute();
            $stmt->close();
$notificationSaved = addNotification(
    $conn,
    $user_id,
    "Deposit Successful",
    "Your deposit of Rs. " .
        number_format((float) $amount, 2) .
        " has been completed successfully.",
    "success"
);

if (!$notificationSaved) {
    throw new Exception("Could not save deposit notification.");
}

$conn->commit();
            $conn->commit();

            $message = "Deposit successful! Your balance has been updated.";
            $messageType = "success";

            // Prevent accidental repeat submission on refresh.
            $_SESSION["deposit_csrf"] = bin2hex(random_bytes(32));

        } catch (Throwable $e) {
            $conn->rollback();
            error_log($e->getMessage());

            $message = "Deposit could not be completed. Please try again.";
            $messageType = "error";
        }
    }
}

// Get the latest account balance.
$stmt = $conn->prepare(
    "SELECT name, account_number, balance
     FROM users
     WHERE id = ?"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$account = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$account) {
    header("Location: logout.php");
    exit();
}

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deposit Money | SecureBank</title>
    <link rel="stylesheet" href="css/deposit.css">
</head>
<body>

<div class="layout">
    <aside class="sidebar">
        <div class="brand">◆ SecureBank</div>
        <p class="brand-subtitle">Digital Banking</p>

        <nav>
            <a href="dashboard.php">▦ Dashboard</a>
            <a class="active" href="deposit.php">＋ Deposit</a>
            <a href="withdraw.php">↓ Withdraw</a>
            <a href="transfer.php">↗ Transfer</a>
            <a href="transactions.php">▤ Transactions</a>
            <a href="profile.php">♙ My Profile</a>
        </nav>

        <a class="logout" href="logout.php">↪ Logout</a>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <div>
                <p class="eyebrow">ACCOUNT SERVICES</p>
                <h1>Deposit Money</h1>
                <p class="welcome">Hello, <?= e($account["name"]) ?>!</p>
            </div>

            <div class="account-badge">
                <span>Account Number</span>
                <strong><?= e($account["account_number"]) ?></strong>
            </div>
        </header>

        <section class="balance-card">
            <div>
                <p>Current Available Balance</p>
                <h2>₹<?= number_format((float) $account["balance"], 2) ?></h2>
                <span class="balance-note">Your latest account balance</span>
            </div>
            <div class="balance-icon">₹</div>
        </section>

        <section class="deposit-card">
            <div class="card-heading">
                <div class="deposit-icon">＋</div>
                <div>
                    <h2>Add Money</h2>
                    <p>Enter the amount you want to deposit.</p>
                </div>
            </div>

            <?php if ($message !== ""): ?>
                <div class="notice <?= e($messageType) ?>" role="status">
                    <?= e($message) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="deposit.php">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= e($_SESSION["deposit_csrf"]) ?>"
                >

                <label for="amount">Deposit Amount (₹)</label>
                <div class="amount-input">
                    <span>₹</span>
                    <input
                        type="number"
                        id="amount"
                        name="amount"
                        min="0.01"
                        max="1000000"
                        step="0.01"
                        placeholder="Enter amount"
                        required
                    >
                </div>

                <p class="field-help">
                    Minimum ₹0.01 · Maximum ₹10,00,000 per transaction
                </p>

                <label for="description">Description (optional)</label>
                <input
                    class="text-input"
                    type="text"
                    id="description"
                    name="description"
                    maxlength="255"
                    placeholder="e.g. Savings deposit"
                >

                <button class="deposit-button" type="submit">
                    Confirm Deposit <span>→</span>
                </button>
            </form>

            <div class="security-note">
                <span>✓</span>
                Your deposit and transaction record are saved together.
            </div>
        </section>

        <footer>© 2026 SecureBank · Bank Management System</footer>
    </main>
</div>

</body>
</html>
