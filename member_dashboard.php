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

// Get member contributions
$contributions_query = "SELECT * FROM contribution WHERE member_id = '$member_id' ORDER BY contribution_date DESC LIMIT 5";
$contributions = $conn->query($contributions_query);

// Get upcoming meetings
$meetings_query = "SELECT * FROM meeting WHERE meeting_date >= CURDATE() ORDER BY meeting_date ASC LIMIT 3";
$meetings = $conn->query($meetings_query);

// Get all loans for this member
$loans_query = "SELECT * FROM loan WHERE member_id = '$member_id' ORDER BY application_date DESC";
$loans = $conn->query($loans_query);

// Calculate interest rate based on amount
function calculateInterestRate($amount) {
    if ($amount <= 5000) {
        return 8.5;
    } elseif ($amount <= 10000) {
        return 7.5;
    } elseif ($amount <= 20000) {
        return 6.5;
    } else {
        return 5.5;
    }
}

// Handle loan request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_loan'])) {
    $amount = $conn->real_escape_string($_POST['amount']);
    $repayment_date = $conn->real_escape_string($_POST['repayment_date']);
    
    // Auto-calculate interest rate
    $interest_rate = calculateInterestRate($amount);
    
    // Generate loan ID
    $loan_id = 'LN' . date('Ym') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    $insert_query = "INSERT INTO loan (loan_id, member_id, amount, interest_rate, status, application_date, repayment_date)
                     VALUES ('$loan_id', '$member_id', '$amount', '$interest_rate', 'Pending', CURDATE(), '$repayment_date')";
    
    if ($conn->query($insert_query)) {
        $loan_message = "Loan request submitted successfully! Your Loan ID: $loan_id";
    } else {
        $loan_error = "Error submitting loan request: " . $conn->error;
    }
    
    // Refresh loans list
    $loans = $conn->query($loans_query);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Dashboard - WGMS</title>
    <link rel="stylesheet" href="css/member_dashboard.css">
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>WGMS Member</h2>
            <p>Welcome, <?php echo $member['first_name']; ?></p>
        </div>
        <ul class="sidebar-menu">
            <li class="active"><a href="member_dashboard.php">Dashboard</a></li>
            <li><a href="my_contributions.php">My Contributions</a></li>
            <li><a href="meeting_schedule.php">Meeting Schedule</a></li>
            <li><a href="profile.php">My Profile</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Member Dashboard</h1>
            <button class="logout-btn" onclick="logout()">Logout</button>
        </div>

        <div class="profile-container">
            <div class="profile-header">
                <div class="profile-info">
                    <div class="profile-avatar"><?php echo strtoupper(substr($member['first_name'], 0, 1)); ?></div>
                    <div class="profile-details">
                        <h2><?php echo $member['first_name'] . ' ' . $member['last_name']; ?></h2>
                        <p>Member ID: <?php echo $member['member_id']; ?></p>
                    </div>
                </div>
                <div class="balance-card">
                    <div class="label">Account Balance</div>
                    <div class="amount">KSH <?php echo number_format($member['balance'], 2); ?></div>
                </div>
            </div>
        </div>

        <div class="content-section">
            <div class="contributions-container">
                <div class="section-header">
                    <h2>Recent Contributions</h2>
                    <a href="my_contributions.php">View All</a>
                </div>
                <?php if ($contributions->num_rows > 0): ?>
                    <?php while($contribution = $contributions->fetch_assoc()): ?>
                        <div class="contribution-item">
                            <div class="contribution-date"><?php echo date('F j, Y', strtotime($contribution['contribution_date'])); ?></div>
                            <div>Amount: KSH <?php echo number_format($contribution['amount'], 2); ?></div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No contributions found.</p>
                <?php endif; ?>
            </div>

            <div class="meetings-container">
                <div class="section-header">
                    <h2>Upcoming Meetings</h2>
                    <a href="meeting_schedule.php">View All</a>
                </div>
                <?php if ($meetings->num_rows > 0): ?>
                    <?php while($meeting = $meetings->fetch_assoc()): ?>
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

        <div class="loans-container">
            <div class="section-header">
                <h2 id="loans">Loan Management</h2>
            </div>
            
            <?php if (isset($loan_message)): ?>
                <div class="message success-message"><?php echo $loan_message; ?></div>
            <?php endif; ?>
            <?php if (isset($loan_error)): ?>
                <div class="message error-message"><?php echo $loan_error; ?></div>
            <?php endif; ?>
            
            <div class="loan-form-container">
                <h3>Request New Loan</h3>
                <form method="POST" action="" id="loanForm">
                    <div class="loan-calculator">
                        <h4>Loan Calculator</h4>
                        <div class="form-group">
                            <label for="amount">Enter Loan Amount (KSH):</label>
                            <input type="number" id="loanAmount" name="amount" min="1000" step="100" required 
                                   oninput="calculateLoanTerms()">
                        </div>
                        
                        <div class="calculator-results">
                            <div class="calculator-row">
                                <span class="calculator-label">Interest Rate:</span>
                                <span class="interest-rate-display" id="interestRateDisplay">0%</span>
                            </div>
                            <div class="calculator-row">
                                <span class="calculator-label">Total Interest:</span>
                                <span class="calculator-value" id="totalInterestDisplay">KSH 0</span>
                            </div>
                            <div class="calculator-row">
                                <span class="calculator-label">Total Repayment:</span>
                                <span class="calculator-value" id="totalRepaymentDisplay">KSH 0</span>
                            </div>
                        </div>
                    </div>
                    
                    <input type="hidden" id="calculatedInterest" name="interest_rate">
                    
                    <div class="form-group">
                        <label for="repayment_date">Repayment Due Date:</label>
                        <input type="date" name="repayment_date" min="<?php echo date('Y-m-d', strtotime('+1 month')); ?>" required>
                    </div>
                    <button type="submit" name="request_loan" class="submit-loan-btn">Submit Loan Request</button>
                </form>
            </div>
            
            <div class="loan-status-section">
                <h3>Your Loan Applications</h3>
                <?php if ($loans->num_rows > 0): ?>
                    <div class="loan-list">
                        <?php while($loan = $loans->fetch_assoc()): ?>
                            <div class="loan-item">
                                <div class="loan-header">
                                    <span class="loan-amount">KSH <?php echo number_format($loan['amount'], 2); ?></span>
                                    <span class="loan-status status-<?php echo strtolower($loan['status']); ?>">
                                        <?php echo $loan['status']; ?>
                                    </span>
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
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p>You have no loan applications yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="js/member_dashboard.js"></script>
</body>
</html>