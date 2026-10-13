<?php
session_start();
require_once "Config/db.php";
require_once "Config/notifications.php";
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$userId = (int) $_SESSION["user_id"];
$error = "";
$success = "";

if (empty($_SESSION["transfer_csrf"])) {
    $_SESSION["transfer_csrf"] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST["csrf_token"] ?? "";
    $receiverAccount = trim($_POST["receiver_account"] ?? "");
    $amountInput = trim($_POST["amount"] ?? "");
    $description = trim($_POST["description"] ?? "");

    if (!hash_equals($_SESSION["transfer_csrf"], $token)) {
        $error = "Invalid request. Please refresh the page and try again.";
    
} elseif (
    $receiverAccount === "" ||
    !preg_match('/^AC\d{8}$/', $receiverAccount)
) {
    $error = "Please enter a valid receiver account number.";

    } elseif (!preg_match('/^\d+(\.\d{1,2})?$/', $amountInput)
        || (float)$amountInput <= 0) {
        $error = "Please enter a valid amount greater than zero.";
    } elseif (strlen($description) > 200) {
        $error = "Description cannot exceed 200 characters.";
    } else {
        $amount = (float)$amountInput;

        try {
            $conn->begin_transaction();

            // Lock the sender's account.
            $stmt = $conn->prepare(
                "SELECT id, account_number, balance
                 FROM users WHERE id = ? FOR UPDATE"
            );
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $sender = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$sender) {
                throw new Exception("Sender account not found.");
            }

            // Lock the receiver's account.
            $stmt = $conn->prepare(
                "SELECT id, account_number, balance
                 FROM users WHERE account_number = ? FOR UPDATE"
            );
            $stmt->bind_param("s", $receiverAccount);
            $stmt->execute();
            $receiver = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$receiver) {
                throw new Exception("Receiver account number does not exist.");
            }

            if ((int)$receiver["id"] === $userId) {
                throw new Exception("You cannot transfer money to your own account.");
            }

            if ($amount > (float)$sender["balance"]) {
                throw new Exception("Insufficient balance for this transfer.");
            }

            // Deduct money from the sender.
            $stmt = $conn->prepare(
                "UPDATE users
                 SET balance = balance - ?
                 WHERE id = ? AND balance >= ?"
            );
            $stmt->bind_param("did", $amount, $userId, $amount);
            $stmt->execute();

            if ($stmt->affected_rows !== 1) {
                $stmt->close();
                throw new Exception("Transfer failed. Please check your balance.");
            }
            $stmt->close();

            // Add money to the receiver.
            $receiverId = (int)$receiver["id"];
            $stmt = $conn->prepare(
                "UPDATE users SET balance = balance + ? WHERE id = ?"
            );
            $stmt->bind_param("di", $amount, $receiverId);
            $stmt->execute();

            if ($stmt->affected_rows !== 1) {
                $stmt->close();
                throw new Exception("Could not update receiver balance.");
            }
            $stmt->close();

            // Record the sender's transaction.
            $senderDescription = $description !== ""
                ? $description
                : "Transfer to account " . $receiver["account_number"];

            $senderType = "Transfer";
            $status = "Success";
            $senderAccount = $sender["account_number"];

            $stmt = $conn->prepare(
                "INSERT INTO transactions
                 (account_number, transaction_type, amount, description, status)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->bind_param(
                "ssdss",
                $senderAccount,
                $senderType,
                $amount,
                $senderDescription,
                $status
            );
            $stmt->execute();
            $stmt->close();

            // Record the receiver's incoming money.
            $receiverType = "Deposit";
            $receiverDescription =
                "Transfer received from account " . $sender["account_number"];

            $stmt = $conn->prepare(
                "INSERT INTO transactions
                 (account_number, transaction_type, amount, description, status)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->bind_param(
                "ssdss",
                $receiverAccount,
                $receiverType,
                $amount,
                $receiverDescription,
                $status
            );
            $stmt->execute();
            $stmt->close();
$receiverId = (int) $receiver["id"];

$senderNotificationSaved = addNotification(
    $conn,
    $userId,
    "Transfer Successful",
    "Your transfer of Rs. " .
        number_format((float) $amount, 2) .
        " to account " .
        $receiver["account_number"] .
        " was successful.",
    "success"
);

$receiverNotificationSaved = addNotification(
    $conn,
    $receiverId,
    "Money Received",
    "You received Rs. " .
        number_format((float) $amount, 2) .
        " from account " .
        $sender["account_number"] . ".",
    "success"
);

if (!$senderNotificationSaved || !$receiverNotificationSaved) {
    throw new Exception("Could not save transfer notifications.");
}

$conn->commit();
            $conn->commit();

            $success = "₹" . number_format($amount, 2)
                . " transferred successfully to account "
                . $receiverAccount . ".";

            // Prevent resubmission and rotate the token.
            $_SESSION["transfer_csrf"] = bin2hex(random_bytes(32));
        } catch (Throwable $e) {
            $conn->rollback();

            // Show expected validation errors; log unexpected database errors.
            if ($e instanceof Exception) {
                $error = $e->getMessage();
            } else {
                error_log("Transfer error: " . $e->getMessage());
                $error = "Something went wrong. Please check your database tables and try again.";
            }
        }
    }
}

// Fetch current balance for display.
$stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$account = $stmt->get_result()->fetch_assoc();
$stmt->close();

$currentBalance = $account ? (float)$account["balance"] : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Money Transfer | Bank Management System</title>
    <link rel="stylesheet" href="css/transfer.css">
</head>
<body>

<div class="transfer-container">
    <div class="transfer-header">
        <a class="back-link" href="dashboard.php">← Back to Dashboard</a>
        <h1>Money Transfer</h1>
        <p>Transfer money securely to another account.</p>
    </div>

    <div class="balance-card">
        <span>Available Balance</span>
        <h2>₹<?= number_format($currentBalance, 2) ?></h2>
    </div>

    <?php if ($error !== ""): ?>
        <div class="message error">
            <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
        </div>
    <?php endif; ?>

    <?php if ($success !== ""): ?>
        <div class="message success">
            <?= htmlspecialchars($success, ENT_QUOTES, "UTF-8") ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="transfer.php" class="transfer-form">
        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars($_SESSION["transfer_csrf"], ENT_QUOTES, "UTF-8") ?>"
        >

        <label for="receiver_account">Receiver Account Number</label>
        <input
            type="text"
            id="receiver_account"
            name="receiver_account"
            placeholder="Enter receiver's account number"
            inputmode="numeric"
            required
        >

        <label for="amount">Transfer Amount (₹)</label>
        <input
            type="number"
            id="amount"
            name="amount"
            placeholder="Enter amount"
            min="0.01"
            step="any"
            required
        >

        <label for="description">Description (optional)</label>
        <input
            type="text"
            id="description"
            name="description"
            maxlength="200"
            placeholder="e.g. College project testing"
        >

        <button type="submit">Transfer Money</button>
    </form>

    <div class="notice">
        Please verify the receiver's account number before transferring money.
    </div>
</div>

</body>
</html>
