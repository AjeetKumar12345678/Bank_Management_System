
<?php
session_start();
require_once "Config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION["user_id"];

if (empty($_SESSION["withdraw_csrf"])) {
    $_SESSION["withdraw_csrf"] = bin2hex(random_bytes(32));
}

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST["csrf_token"] ?? "";
    $amountInput = trim($_POST["amount"] ?? "");
    $description = trim($_POST["description"] ?? "");

    if (!hash_equals($_SESSION["withdraw_csrf"], $token)) {
        $message = "Invalid request. Refresh the page and try again.";
        $messageType = "error";
    } elseif (
        !preg_match('/^\d{1,8}(\.\d{1,2})?$/', $amountInput)
        || (float) $amountInput <= 0
    ) {
        $message = "Enter a valid withdrawal amount.";
        $messageType = "error";
    } elseif ((float) $amountInput > 1000000) {
        $message = "Maximum withdrawal per transaction is ₹10,00,000.";
        $messageType = "error";
    } else {
        $amount = number_format((float) $amountInput, 2, ".", "");

        try {
            $conn->begin_transaction();

            // Lock account row to prevent concurrent balance changes.
            $stmt = $conn->prepare(
                "SELECT account_number, balance
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

            if ((float) $account["balance"] < (float) $amount) {
                $conn->rollback();
                $message = "Insufficient balance. Withdrawal not completed.";
                $messageType = "error";
            } else {
                // Deduct money only when sufficient balance exists.
                $stmt = $conn->prepare(
                    "UPDATE users
                     SET balance = balance - ?
                     WHERE id = ? AND balance >= ?"
                );
                $stmt->bind_param("did", $amount, $user_id, $amount);
                $stmt->execute();

                if ($stmt->affected_rows !== 1) {
                    $stmt->close();
                    throw new Exception("Balance update failed.");
                }
                $stmt->close();

                $type = "Withdrawal";
                $status = "Success";
                $details = $description !== ""
                    ? substr($description, 0, 255)
                    : "Cash withdrawal";

                // Save transaction history entry.
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

                $conn->commit();

                $message = "Withdrawal successful! Your balance has been updated.";
                $messageType = "success";

                $_SESSION["withdraw_csrf"] = bin2hex(random_bytes(32));
            }
        } catch (Throwable $e) {
            try {
                $conn->rollback();
            } catch (Throwable $ignored) {
                // Transaction may already have been rolled back.
            }

            error_log($e->getMessage());
            $message = "Withdrawal could not be completed. Please try again.";
            $messageType = "error";
        }
    }
}

// Get updated account information.
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
    <title>Withdraw Money | SecureBank</title>
    <link rel="stylesheet" href="css/withdraw.css">
</head>
<body>

<div class="layout">
    <aside class="sidebar">
        <div class="brand">◆ SecureBank</div>
        <p class="brand-subtitle">Digital Banking</p>

        <nav>
            <a href="dashboard.php">▦ Dashboard</a>
            <a href="deposit.php">＋ Deposit</a>
            <a class="active" href="withdraw.php">↓ Withdraw</a>
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
                <h1>Withdraw Money</h1>
                <p class="welcome">Hello, <?= e($account["name"]) ?>!</p>
            </div>

            <div class="account-badge">
                <span>Account Number</span>
                <strong><?= e($account["account_number"]) ?></strong>
            </div>
        </header>

        <section class="balance-card">
            <div>
                <p>Available Balance</p>
                <h2>₹<?= number_format((float) $account["balance"], 2) ?></h2>
                <span class="balance-note">Check your balance before withdrawing</span>
            </div>
            <div class="balance-icon">₹</div>
        </section>

        <section class="withdraw-card">
            <div class="card-heading">
                <div class="withdraw-icon">↓</div>
                <div>
                    <h2>Withdraw Funds</h2>
                    <p>Enter the amount to deduct from your account.</p>
                </div>
            </div>

            <?php if ($message !== ""): ?>
                <div class="notice <?= e($messageType) ?>" role="status">
                    <?= e($message) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="withdraw.php">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= e($_SESSION["withdraw_csrf"]) ?>"
                >

                <label for="amount">Withdrawal Amount (₹)</label>
                <div class="amount-input">
                    <span>₹</span>

<input
    type="number"
    name="amount"
    id="amount"
    placeholder="Enter withdrawal amount"
    min="0.01"
    step="any"
    required
>
                </div>

                <p class="field-help">
                    Maximum ₹10,00,000 per transaction.
                    Withdrawal is allowed only when sufficient balance exists.
                </p>

                <label for="description">Description (optional)</label>
                <input
                    class="text-input"
                    type="text"
                    id="description"
                    name="description"
                    maxlength="255"
                    placeholder="e.g. Personal expenses"
                >

                <button class="withdraw-button" type="submit">
                    Confirm Withdrawal <span>→</span>
                </button>
            </form>

            <div class="security-note">
                <span>✓</span>
                Insufficient balance requests are rejected.
            </div>
        </section>

        <footer>© 2026 SecureBank · Bank Management System</footer>
    </main>
</div>

</body>
</html>
