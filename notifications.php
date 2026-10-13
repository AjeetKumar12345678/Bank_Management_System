
<?php
session_start();
require_once "Config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$userId = (int) $_SESSION["user_id"];

if (empty($_SESSION["notification_csrf"])) {
    $_SESSION["notification_csrf"] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION["notification_csrf"];
$error = "";

// Mark one notification or all notifications as read
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $postedToken = $_POST["csrf_token"] ?? "";

    if (!is_string($postedToken) ||
        !hash_equals($csrfToken, $postedToken)) {
        http_response_code(403);
        exit("Invalid request. Please refresh the page.");
    }

    $action = $_POST["action"] ?? "";

    if ($action === "mark_all") {
        $stmt = $conn->prepare(
            "UPDATE notifications
             SET is_read = 1
             WHERE user_id = ? AND is_read = 0"
        );
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();

    } elseif ($action === "mark_one") {
        $notificationId = filter_input(
            INPUT_POST,
            "notification_id",
            FILTER_VALIDATE_INT
        );

        if ($notificationId) {
            $stmt = $conn->prepare(
                "UPDATE notifications
                 SET is_read = 1
                 WHERE id = ? AND user_id = ?"
            );
            $stmt->bind_param(
                "ii",
                $notificationId,
                $userId
            );
            $stmt->execute();
            $stmt->close();
        }
    }

    header("Location: notifications.php");
    exit;
}

// Get unread count
$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM notifications
     WHERE user_id = ? AND is_read = 0"
);
$stmt->bind_param("i", $userId);
$stmt->execute();
$unreadCount = (int) $stmt->get_result()
    ->fetch_assoc()["total"];
$stmt->close();

// Get latest 100 notifications for this user
$stmt = $conn->prepare(
    "SELECT id, title, message, type, is_read, created_at
     FROM notifications
     WHERE user_id = ?
     ORDER BY created_at DESC, id DESC
     LIMIT 100"
);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$notifications = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

function h($value): string {
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        "UTF-8"
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications | Bank Management System</title>
    <link rel="stylesheet" href="css/notifications.css">
</head>
<body>
<main class="notification-container">
    <header class="page-header">
        <div>
            <p class="eyebrow">BANK MANAGEMENT SYSTEM</p>
            <h1>Notifications</h1>
            <p class="subtitle">
                Stay updated with your account activity.
            </p>
        </div>

        <a class="back-link" href="dashboard.php">
            Back to Dashboard
        </a>
    </header>

    <section class="summary">
        <div>
            <span class="summary-label">Unread notifications</span>
            <strong><?php echo $unreadCount; ?></strong>
        </div>

        <?php if ($unreadCount > 0): ?>
            <form method="POST">
                <input type="hidden" name="csrf_token"
                       value="<?php echo h($csrfToken); ?>">
                <input type="hidden" name="action" value="mark_all">
                <button class="mark-all" type="submit">
                    Mark all as read
                </button>
            </form>
        <?php endif; ?>
    </section>

    <section class="notification-list">
        <?php if (empty($notifications)): ?>
            <div class="empty-state">
                <div class="empty-icon">&#128276;</div>
                <h2>No notifications yet</h2>
                <p>Your account alerts will appear here.</p>
            </div>
        <?php else: ?>
            <?php foreach ($notifications as $item): ?>
                <article class="notification-card
                    <?php echo ((int)$item["is_read"] === 0)
                        ? "unread" : ""; ?>">
                    <div class="notification-icon
                        <?php echo h($item["type"]); ?>">
                        <?php
                        echo $item["type"] === "success"
                            ? "&#10003;"
                            : ($item["type"] === "warning"
                                ? "!" : "i");
                        ?>
                    </div>

                    <div class="notification-content">
                        <div class="notification-title-row">
                            <h2><?php echo h($item["title"]); ?></h2>
                            <?php if ((int)$item["is_read"] === 0): ?>
                                <span class="unread-label">NEW</span>
                            <?php endif; ?>
                        </div>

                        <p><?php echo h($item["message"]); ?></p>

                        <time>
                            <?php
                            echo h(
                                date(
                                    "d-m-Y h:i A",
                                    strtotime($item["created_at"])
                                )
                            );
                            ?>
                        </time>
                    </div>

                    <?php if ((int)$item["is_read"] === 0): ?>
                        <form method="POST" class="read-form">
                            <input type="hidden" name="csrf_token"
                                   value="<?php echo h($csrfToken); ?>">
                            <input type="hidden" name="action"
                                   value="mark_one">
                            <input type="hidden" name="notification_id"
                                   value="<?php echo (int)$item["id"]; ?>">
                            <button type="submit" class="read-button">
                                Mark read
                            </button>
                        </form>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
