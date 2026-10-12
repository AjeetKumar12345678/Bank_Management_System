
<?php
session_start();
require_once "Config/db.php";

// Check whether the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION["user_id"];

// Fetch logged-in user's account details
$stmt = $conn->prepare(
    "SELECT account_number, name, balance
     FROM users
     WHERE id = ?"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$account = $result->fetch_assoc();
$stmt->close();

if (!$account) {
    header("Location: logout.php");
    exit();
}

// Fetch transactions for this account only
$transactions = [];
$error = "";

try {
    $stmt = $conn->prepare(
        "SELECT id, transaction_type, amount,
                description, status, created_at
         FROM transactions
         WHERE account_number = ?
         ORDER BY created_at DESC, id DESC"
    );

    $stmt->bind_param("s", $account["account_number"]);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }

    $stmt->close();
} catch (mysqli_sql_exception $e) {
    error_log($e->getMessage());
    $error = "Transactions could not be loaded. Please try again.";
}

// Safely display database values in HTML
function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History | Bank Management System</title>
    <link rel="stylesheet" href="css/transactions.css">
</head>
<body>

<div class="layout">

    <aside class="sidebar">
        <div class="brand">◆ SecureBank</div>
        <p class="brand-subtitle">Digital Banking</p>

        <nav>
            <a href="dashboard.php">▦ Dashboard</a>
            <a href="deposit.php">＋ Deposit</a>
            <a href="withdraw.php">↓ Withdraw</a>
            <a href="transfer.php">↗ Transfer</a>
            <a class="active" href="transactions.php">▤ Transactions</a>
            <a href="profile.php">♙ My Profile</a>
        </nav>

        <a class="logout" href="logout.php">↪ Logout</a>
<a href="download_statement.php" class="download-btn">
    ↓ Download Statement
</a>
    </aside>

    <main class="main-content">

        <header class="topbar">
            <div>
                <p class="eyebrow">YOUR ACCOUNT ACTIVITY</p>
                <h1>Transaction History</h1>
                <p class="welcome">
                    Welcome, <?= e($account["name"]) ?>
                </p>
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
                <span class="balance-note">Your current account balance</span>
            </div>
            <div class="balance-icon">₹</div>
        </section>

        <section class="history-card">
            <div class="section-heading">
                <div>
                    <h2>All Transactions</h2>
                    <p>Review your recent account activity.</p>
                </div>

                <span class="count">
                    <?= count($transactions) ?> records
                </span>
            </div>

            <?php if ($error !== ""): ?>

                <div class="notice error"><?= e($error) ?></div>

            <?php elseif (empty($transactions)): ?>

                <div class="empty-state">
                    <div class="empty-icon">▤</div>
                    <h3>No transactions yet</h3>
                    <p>
                        Your deposits, withdrawals and transfers
                        will appear here when recorded.
                    </p>
                    <a class="primary-btn" href="deposit.php">
                        Go to Deposit
                    </a>
                </div>

            <?php else: ?>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Transaction ID</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Date &amp; Time</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php foreach ($transactions as $tx): ?>
                            <?php
                            $typeClass = strtolower(
                                (string) $tx["transaction_type"]
                            );
                            $statusClass = strtolower(
                                (string) $tx["status"]
                            );
                            ?>

                            <tr>
                                <td class="transaction-id">
                                    #<?= (int) $tx["id"] ?>
                                </td>

                                <td>
                                    <span class="type <?= e($typeClass) ?>">
                                        <?= e($tx["transaction_type"]) ?>
                                    </span>
                                </td>

                                <td class="description">
                                    <?= e(
                                        $tx["description"] ?: "Bank transaction"
                                    ) ?>
                                </td>

                                <td>
                                    <?= e(
                                        date(
                                            "d M Y, h:i A",
                                            strtotime($tx["created_at"])
                                        )
                                    ) ?>
                                </td>

                                <td class="amount">
                                    ₹<?= number_format(
                                        (float) $tx["amount"], 2
                                    ) ?>
                                </td>

                                <td>
                                    <span class="status <?= e($statusClass) ?>">
                                        <?= e($tx["status"]) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php endif; ?>
        </section>

        <footer>
            © 2026 SecureBank · Bank Management System
        </footer>

    </main>
</div>

</body>
</html>
