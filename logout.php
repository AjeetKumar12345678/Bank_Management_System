```php
<?php
session_start();

// Remove all session data
$_SESSION = [];

// Destroy the current session
session_destroy();

// Prevent the browser from showing cached private pages
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// Redirect to login page
header("Location: login.php?logout=success");
exit;
?>
```