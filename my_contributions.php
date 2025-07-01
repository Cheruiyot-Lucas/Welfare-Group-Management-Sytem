<?php
session_start();
if (!isset($_SESSION['member_id'])) {
    header("Location: index.html");
    exit();
}

include 'config.php';

// Get member details
$member_id = $_SESSION['member_id'];
$member_query = "SELECT m.*, a.balance FROM member m 
                 LEFT JOIN account a ON m.member_id = a.member_id 
                 WHERE m.member_id = '$member_id'";
$member_result = $conn->query($member_query);
$member = $member_result->fetch_assoc();

// Get all contributions for the member
$contributions_query = "SELECT * FROM contribution WHERE member_id = '$member_id' ORDER BY contribution_date DESC";
$contributions = $conn->query($contributions_query);

// Calculate total contributions
$total_contributions = $conn->query("SELECT SUM(amount) as total FROM contribution WHERE member_id = '$member_id'")->fetch_assoc()['total'];

// Handle new contribution submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_contribution'])) {
    $amount = $_POST['amount'];
    $date = $_POST['contribution_date'];
    $notes = $_POST['notes'] ?? '';
    
    // Validate amount
    if (!is_numeric($amount) || $amount <= 0) {
        $error = "Please enter a valid contribution amount";
    } else {
        // Generate contribution ID
        $contribution_id = 'C' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        
        // Insert contribution
        $insert_query = "INSERT INTO contribution (contribution_id, member_id, contribution_date, amount, notes) 
                         VALUES ('$contribution_id', '$member_id', '$date', $amount, '" . $conn->real_escape_string($notes) . "')";
        
        if ($conn->query($insert_query)) {
            // Update member's account balance
            $update_query = "UPDATE account SET balance = balance + $amount WHERE member_id = '$member_id'";
            $conn->query($update_query);
            
            $success = "Contribution submitted successfully!";
            // Refresh the page to show the new contribution
            header("Location: my_contributions.php");
            exit();
        } else {
            $error = "Error submitting contribution: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Contributions - WGMS</title>
    <link rel="stylesheet" href="css/my_contributions.css">
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>WGMS Member</h2>
            <p>Welcome, <?php echo $member['first_name']; ?></p>
        </div>
        <ul class="sidebar-menu">
            <li><a href="member_dashboard.php">Dashboard</a></li>
            <li class="active"><a href="my_contributions.php">My Contributions</a></li>
            <li><a href="meeting_schedule.php">Meeting Schedule</a></li>
            <li><a href="profile.php">My Profile</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>My Contributions</h1>
            <button class="logout-btn" onclick="logout()">Logout</button>
        </div>

        <div class="contributions-container">
            <div class="summary-card">
                <div class="summary-item">
                    <div class="label">Account Balance</div>
                    <div class="value">KSH <?php echo number_format($member['balance'], 2); ?></div>
                </div>
                <div class="summary-item">
                    <div class="label">Total Contributions</div>
                    <div class="value">KSH <?php echo number_format($total_contributions, 2); ?></div>
                </div>
                <div class="summary-item">
                    <div class="label">Total Contributions Count</div>
                    <div class="value"><?php echo $contributions->num_rows; ?></div>
                </div>
            </div>

            <button class="toggle-form-btn" onclick="toggleContributionForm()">Make New Contribution</button>

            <div id="contribution-form" class="new-contribution-form hidden">
                <h2>New Contribution</h2>
                <?php if (isset($error)): ?>
                    <div class="error"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if (isset($success)): ?>
                    <div class="success"><?php echo $success; ?></div>
                <?php endif; ?>
                <form method="POST" action="my_contributions.php">
                    <div class="form-group">
                        <label for="amount">Amount (KSH)</label>
                        <input type="number" id="amount" name="amount" min="1" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="contribution_date">Date</label>
                        <input type="date" id="contribution_date" name="contribution_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="notes">Notes (Optional)</label>
                        <textarea id="notes" name="notes"></textarea>
                    </div>
                    <button type="submit" name="submit_contribution" class="submit-btn">Submit Contribution</button>
                </form>
            </div>

            <h2>Contribution History</h2>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Contribution ID</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($contributions->num_rows > 0): ?>
                        <?php while($contribution = $contributions->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('F j, Y', strtotime($contribution['contribution_date'])); ?></td>
                                <td>KSH <?php echo number_format($contribution['amount'], 2); ?></td>
                                <td><?php echo $contribution['contribution_id']; ?></td>
                                <td><?php echo $contribution['notes'] ? $contribution['notes'] : '-'; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No contributions found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="js/my_contributions.js"></script>
</body>
</html>