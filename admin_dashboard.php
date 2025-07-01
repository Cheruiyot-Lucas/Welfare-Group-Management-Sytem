<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.html");
    exit();
}

include 'config.php';

// Get admin details
$admin_id = $_SESSION['admin_id'];
$admin_query = "SELECT * FROM admin WHERE admin_id = '$admin_id'";
$admin_result = $conn->query($admin_query);
$admin = $admin_result->fetch_assoc();

// Get statistics
$members_count = $conn->query("SELECT COUNT(*) as count FROM member")->fetch_assoc()['count'];
$contributions_sum = $conn->query("SELECT SUM(amount) as total FROM contribution")->fetch_assoc()['total'];
$upcoming_meetings = $conn->query("SELECT * FROM meeting WHERE meeting_date >= CURDATE() ORDER BY meeting_date ASC LIMIT 3");

// Get pending loans count for stats
$pending_loans_count = $conn->query("SELECT COUNT(*) as count FROM loan WHERE status = 'Pending'")->fetch_assoc()['count'];
$total_loans_amount = $conn->query("SELECT SUM(amount) as total FROM loan WHERE status = 'Approved'")->fetch_assoc()['total'];

// Get pending loans for approval section
$pending_loans = $conn->query("SELECT l.*, m.first_name, m.last_name FROM loan l JOIN member m ON l.member_id = m.member_id WHERE l.status = 'Pending' ORDER BY l.application_date ASC");

// Handle loan approval/rejection
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['approve_loan'])) {
        $loan_id = $conn->real_escape_string($_POST['loan_id']);
        $update_query = "UPDATE loan SET status = 'Approved', approved_by = '$admin_id', approval_date = NOW() WHERE loan_id = '$loan_id'";
        if ($conn->query($update_query)) {
            $loan_message = "Loan approved successfully!";
        } else {
            $loan_error = "Error approving loan: " . $conn->error;
        }
    } elseif (isset($_POST['reject_loan'])) {
        $loan_id = $conn->real_escape_string($_POST['loan_id']);
        $update_query = "UPDATE loan SET status = 'Rejected', approved_by = '$admin_id', approval_date = NOW() WHERE loan_id = '$loan_id'";
        if ($conn->query($update_query)) {
            $loan_message = "Loan rejected successfully!";
        } else {
            $loan_error = "Error rejecting loan: " . $conn->error;
        }
    }
    
    // Refresh pending loans after action
    $pending_loans = $conn->query("SELECT l.*, m.first_name, m.last_name FROM loan l JOIN member m ON l.member_id = m.member_id WHERE l.status = 'Pending' ORDER BY l.application_date ASC");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - WGMS</title>
    <link rel="stylesheet" href="css/admin_dashboard.css">
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>WGMS Admin</h2>
            <p>Welcome, <?php echo $admin['username']; ?></p>
        </div>
        <ul class="sidebar-menu">
            <li class="active"><a href="admin_dashboard.php">Dashboard</a></li>
            <li><a href="manage_members.php">Manage Members</a></li>
            <li><a href="manage_contributions.php">Manage Contributions</a></li>
            <li><a href="manage_meetings.php">Manage Meetings</a></li>
            <li><a href="generate_reports.php">Generate Reports</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Admin Dashboard</h1>
            <button class="logout-btn" onclick="logout()">Logout</button>
        </div>

        <?php if (isset($loan_message)): ?>
            <div class="message success-message"><?php echo $loan_message; ?></div>
        <?php endif; ?>
        <?php if (isset($loan_error)): ?>
            <div class="message error-message"><?php echo $loan_error; ?></div>
        <?php endif; ?>

        <div class="stats-container">
            <div class="stat-card">
                <h3>Total Members</h3>
                <div class="value"><?php echo $members_count; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Contributions</h3>
                <div class="value">KSH <?php echo number_format($contributions_sum, 2); ?></div>
            </div>
            <div class="stat-card">
                <h3>Active Loans</h3>
                <div class="value">KSH <?php echo number_format($total_loans_amount, 2); ?></div>
            </div>
            <div class="stat-card">
                <h3>Pending Loans</h3>
                <div class="value"><?php echo $pending_loans_count; ?></div>
            </div>
        </div>

        <div class="loans-container">
            <h2>Pending Loan Approvals</h2>
            <?php if ($pending_loans->num_rows > 0): ?>
                <?php while($loan = $pending_loans->fetch_assoc()): ?>
                    <div class="loan-item">
                        <div class="loan-header">
                            <span class="loan-amount">KSH <?php echo number_format($loan['amount'], 2); ?></span>
                            <span class="loan-member"><?php echo $loan['first_name'] . ' ' . $loan['last_name']; ?></span>
                        </div>
                        <div class="loan-details">
                            <div class="loan-detail">
                                <label>Interest Rate:</label>
                                <span><?php echo $loan['interest_rate']; ?>%</span>
                            </div>
                            <div class="loan-detail">
                                <label>Due Date:</label>
                                <span><?php echo date('F j, Y', strtotime($loan['repayment_date'])); ?></span>
                            </div>
                            <div class="loan-detail">
                                <label>Application Date:</label>
                                <span><?php echo date('F j, Y', strtotime($loan['application_date'])); ?></span>
                            </div>
                            <div class="loan-detail">
                                <label>Loan ID:</label>
                                <span><?php echo $loan['loan_id']; ?></span>
                            </div>
                        </div>
                        <form method="POST" class="loan-actions">
                            <input type="hidden" name="loan_id" value="<?php echo $loan['loan_id']; ?>">
                            <button type="submit" name="approve_loan" class="approve-btn">Approve</button>
                            <button type="submit" name="reject_loan" class="reject-btn">Reject</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No pending loan applications.</p>
            <?php endif; ?>
        </div>

        <div class="meetings-container">
            <h2>Upcoming Meetings</h2>
            <?php if ($upcoming_meetings->num_rows > 0): ?>
                <?php while($meeting = $upcoming_meetings->fetch_assoc()): ?>
                    <div class="meeting-item">
                        <div class="meeting-date"><?php echo date('F j, Y', strtotime($meeting['meeting_date'])); ?></div>
                        <div>Venue: <?php echo $meeting['venue']; ?></div>
                        <div>Agenda: <?php echo $meeting['agenda'] ? $meeting['agenda'] : 'Not specified'; ?></div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No upcoming meetings scheduled.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'logout.php';
            }
        }
    </script>
</body>
</html>