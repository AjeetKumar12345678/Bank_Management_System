<?php
// Temporary demo data
// Baad mein ye values database se aayengi.

$user_name = "Ajeet Kumar";
$account_number = "XXXX XXXX 4582";
$balance = 25840.00;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard - Bank Management System</title>

    <link rel="stylesheet" href="css/dashboard.css">
</head>

<body>

<div class="dashboard-container">

    <!-- ================= SIDEBAR ================= -->

    <aside class="sidebar">

        <div class="brand">
            <div class="brand-icon">◆</div>

            <div>
                <h2>SecureBank</h2>
                <small>Digital Banking</small>
            </div>
        </div>

        <nav class="sidebar-menu">

            <a href="dashboard.php" class="menu-item active">
                <span>▣</span>
                Dashboard
            </a>

            <a href="deposit.php" class="menu-item">
                <span>＋</span>
                Deposit
            </a>

            <a href="withdraw.php" class="menu-item">
                <span>↓</span>
                Withdraw
            </a>

            <a href="transfer.php" class="menu-item">
                <span>↗</span>
                Transfer
            </a>

            <a href="transactions.php" class="menu-item">
                <span>▤</span>
                Transactions
            </a>

           <a href="profile.php">My Profile</a>
                
                
            </a>

        </nav>

        <div class="sidebar-bottom">

            <a href="#" class="menu-item">
                <span>⚙</span>
                Settings
            </a>

            <a href="logout.php" class="menu-item logout">
                <span>↪</span>
                Logout
            </a>

        </div>

    </aside>


    <!-- ================= MAIN CONTENT ================= -->

    <main class="main-content">

        <!-- TOP BAR -->

        <header class="topbar">

            <div class="mobile-menu" id="mobileMenu">
                ☰
            </div>

            <div class="page-title">
                <h1>Dashboard</h1>
                <p>Welcome back, <?php echo $user_name; ?>!</p>
            </div>

            <div class="top-actions">

                <button class="notification-btn" id="notificationBtn">
                    🔔
                    <span class="notification-dot"></span>
                </button>

                <div class="user-profile">

                    <div class="avatar">
                        <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                    </div>

                    <div class="user-info">
                        <strong><?php echo $user_name; ?></strong>
                        <small>Customer</small>
                    </div>

                </div>

            </div>

        </header>


        <!-- ================= ACCOUNT SUMMARY ================= -->

        <section class="summary-grid">

            <div class="balance-card">

                <div class="balance-header">
                    <div>
                        <span>Available Balance</span>

                        <h2 id="balance">
                            ₹<?php echo number_format($balance, 2); ?>
                        </h2>
                    </div>

                    <button id="toggleBalance" class="eye-btn">
                        👁
                    </button>
                </div>

                <div class="account-details">

                    <span>Account Number</span>

                    <strong>
                        <?php echo $account_number; ?>
                    </strong>

                </div>

                <div class="balance-footer">

                    <span>Last updated: Today</span>

                    <span class="status">
                        ● Active
                    </span>

                </div>

            </div>


            <div class="summary-card">

                <div class="summary-icon income">
                    ↑
                </div>

                <div>
                    <span>Total Deposits</span>
                    <h3>₹45,500</h3>
                    <small class="positive">
                        +12.5% this month
                    </small>
                </div>

            </div>


            <div class="summary-card">

                <div class="summary-icon expense">
                    ↓
                </div>

                <div>
                    <span>Total Withdrawals</span>
                    <h3>₹19,660</h3>
                    <small class="negative">
                        -4.2% this month
                    </small>
                </div>

            </div>

        </section>


        <!-- ================= QUICK ACTIONS ================= -->

        <section class="section">

            <div class="section-header">

                <div>
                    <h2>Quick Actions</h2>
                    <p>Manage your account easily</p>
                </div>

            </div>


            <div class="quick-actions">

                <a href="deposit.php" class="action-card">
                    <div class="action-icon deposit-icon">＋</div>
                    <strong>Deposit</strong>
                    <span>Add money</span>
                </a>

                <a href="withdraw.php" class="action-card">
                    <div class="action-icon withdraw-icon">↓</div>
                    <strong>Withdraw</strong>
                    <span>Take money out</span>
                </a>

                <a href="transfer.php" class="action-card">
                    <div class="action-icon transfer-icon">↗</div>
                    <strong>Transfer</strong>
                    <span>Send money</span>
                </a>

                <a href="transactions.php" class="action-card">
                    <div class="action-icon history-icon">▤</div>
                    <strong>History</strong>
                    <span>View transactions</span>
                </a>

            </div>

        </section>


        <!-- ================= LOWER GRID ================= -->

        <section class="dashboard-grid">


            <!-- RECENT TRANSACTIONS -->

            <div class="transactions-panel">

                <div class="panel-header">

                    <div>
                        <h2>Recent Transactions</h2>
                        <p>Your latest account activity</p>
                    </div>

                    <a href="transactions.php">
                        View All →
                    </a>

                </div>


                <div class="transaction-list">


                    <div class="transaction">

                        <div class="transaction-left">

                            <div class="transaction-icon deposit">
                                ↑
                            </div>

                            <div>
                                <strong>Salary Credit</strong>
                                <span>Today, 10:30 AM</span>
                            </div>

                        </div>

                        <strong class="amount positive">
                            +₹35,000
                        </strong>

                    </div>


                    <div class="transaction">

                        <div class="transaction-left">

                            <div class="transaction-icon transfer">
                                ↗
                            </div>

                            <div>
                                <strong>Money Transfer</strong>
                                <span>Yesterday, 4:20 PM</span>
                            </div>

                        </div>

                        <strong class="amount negative">
                            -₹5,000
                        </strong>

                    </div>


                    <div class="transaction">

                        <div class="transaction-left">

                            <div class="transaction-icon withdraw">
                                ↓
                            </div>

                            <div>
                                <strong>ATM Withdrawal</strong>
                                <span>28 Sep, 2:15 PM</span>
                            </div>

                        </div>

                        <strong class="amount negative">
                            -₹3,000
                        </strong>

                    </div>


                    <div class="transaction">

                        <div class="transaction-left">

                            <div class="transaction-icon deposit">
                                ↑
                            </div>

                            <div>
                                <strong>Cash Deposit</strong>
                                <span>27 Sep, 11:40 AM</span>
                            </div>

                        </div>

                        <strong class="amount positive">
                            +₹10,000
                        </strong>

                    </div>

                </div>

            </div>


            <!-- SPENDING / ACCOUNT ACTIVITY -->

            <div class="activity-panel">

                <div class="panel-header">

                    <div>
                        <h2>Account Activity</h2>
                        <p>This month</p>
                    </div>

                    <select id="activityFilter">

                        <option>This Month</option>
                        <option>Last Month</option>
                        <option>Last 3 Months</option>

                    </select>

                </div>


                <div class="chart">

                    <div class="chart-bar" style="height: 45%;">
                        <span>Mon</span>
                    </div>

                    <div class="chart-bar" style="height: 65%;">
                        <span>Tue</span>
                    </div>

                    <div class="chart-bar" style="height: 35%;">
                        <span>Wed</span>
                    </div>

                    <div class="chart-bar" style="height: 80%;">
                        <span>Thu</span>
                    </div>

                    <div class="chart-bar" style="height: 55%;">
                        <span>Fri</span>
                    </div>

                    <div class="chart-bar" style="height: 90%;">
                        <span>Sat</span>
                    </div>

                    <div class="chart-bar" style="height: 60%;">
                        <span>Sun</span>
                    </div>

                </div>

                <div class="activity-total">

                    <span>Total Activity</span>

                    <strong>₹54,660</strong>

                </div>

            </div>

        </section>


        <!-- ================= SECURITY NOTICE ================= -->

        <section class="security-notice">

            <div class="security-symbol">
                🔐
            </div>

            <div>

                <h3>Your account is protected</h3>

                <p>
                    Never share your password, OTP or banking
                    information with anyone.
                </p>

            </div>

            <a href="#">
                Security Tips →
            </a>

        </section>


        <!-- FOOTER -->

        <footer>

            <span>
                © 2026 SecureBank - Bank Management System
            </span>

            <span>
                Secure • Reliable • Simple
            </span>

        </footer>

    </main>

</div>


<script src="js/dashboard.js"></script>

</body>
</html>