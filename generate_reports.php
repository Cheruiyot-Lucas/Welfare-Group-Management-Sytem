<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.html");
    exit();
}

include 'config.php';
$admin_id = $_SESSION['admin_id'];
$admin_query = "SELECT * FROM admin WHERE admin_id = '$admin_id'";
$admin_result = $conn->query($admin_query);
$admin = $admin_result->fetch_assoc();

// Handle report generation
if (isset($_GET['generate'])) {
    $report_type = $_GET['report_type'];
    $start_date = $_GET['start_date'] ?? null;
    $end_date = $_GET['end_date'] ?? null;
    
    switch ($report_type) {
        case 'members_list':
            $report_title = "Members List";
            $query = "SELECT m.member_id,m.first_name,m.last_name,m.username,m.national_id,m.phone_number,m.email, a.balance FROM member m LEFT JOIN account a ON m.member_id = a.member_id";
            break;
            
        case 'contributions_summary':
            $report_title = "Contributions Summary";
            $query = "SELECT  m.first_name, m.last_name,m.username,c.*
                      FROM contribution c
                      JOIN member m ON c.member_id = m.member_id";
            
            if ($start_date && $end_date) {
                $query .= " WHERE c.contribution_date BETWEEN '$start_date' AND '$end_date'";
            }
            
            $query .= " ORDER BY c.contribution_date DESC";
            break;
            
        case 'meeting_attendance':
            $report_title = "Meeting Attendance";
            $query = "SELECT * FROM meeting";
            
            if ($start_date && $end_date) {
                $query .= " WHERE meeting_date BETWEEN '$start_date' AND '$end_date'";
            }
            
            $query .= " ORDER BY meeting_date DESC";
            break;
            
        default:
            $error = "Invalid report type selected";
    }
    
    if (!isset($error)) {
        $result = $conn->query($query);
        $report_data = $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Reports - WGMS</title>
    <link rel="stylesheet" href="css/generate_report.css">
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>WGMS Admin</h2>
            <p>Welcome, <?php echo $admin['username']; ?></p>
        </div>
        <ul class="sidebar-menu">
            <li><a href="admin_dashboard.php">Dashboard</a></li>
            <li><a href="manage_members.php">Manage Members</a></li>
            <li><a href="manage_contributions.php">Manage Contributions</a></li>
            <li><a href="manage_meetings.php">Manage Meetings</a></li>
            <li class="active"><a href="generate_reports.php">Generate Reports</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Generate Reports</h1>
            <button class="logout-btn" onclick="logout()">Logout</button>
        </div>

        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="report-form">
            <form method="GET" action="generate_reports.php">
                <div class="form-group">
                    <label for="report_type">Report Type</label>
                    <select id="report_type" name="report_type" required>
                        <option value="">-- Select Report Type --</option>
                        <option value="members_list" <?php echo isset($_GET['report_type']) && $_GET['report_type'] == 'members_list' ? 'selected' : ''; ?>>Members List</option>
                        <option value="contributions_summary" <?php echo isset($_GET['report_type']) && $_GET['report_type'] == 'contributions_summary' ? 'selected' : ''; ?>>Contributions Summary</option>
                        <option value="meeting_attendance" <?php echo isset($_GET['report_type']) && $_GET['report_type'] == 'meeting_attendance' ? 'selected' : ''; ?>>Meeting Attendance</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="start_date">Start Date (Optional)</label>
                    <input type="date" id="start_date" name="start_date" value="<?php echo $_GET['start_date'] ?? ''; ?>">
                </div>
                <div class="form-group">
                    <label for="end_date">End Date (Optional)</label>
                    <input type="date" id="end_date" name="end_date" value="<?php echo $_GET['end_date'] ?? ''; ?>">
                </div>
                <button type="submit" name="generate" value="1">Generate Report</button>
            </form>
        </div>

        <?php if (isset($report_data)): ?>
            <div class="report-results">
                <h2><?php echo $report_title; ?></h2>
                
                <?php if (!empty($report_data)): ?>
                    <table class="report-table">
                        <thead>
                            <tr>
                                <?php foreach (array_keys($report_data[0]) as $column): ?>
                                    <th><?php echo ucwords(str_replace('_', ' ', $column)); ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($report_data as $row): ?>
                                <tr>
                                    <?php foreach ($row as $value): ?>
                                        <td>
                                            <?php 
                                            if (strpos($value, '-') !== false && strtotime($value)) {
                                                echo date('M j, Y', strtotime($value));
                                            } elseif (is_numeric($value) && strpos($value, '.') !== false) {
                                                echo 'KSH ' . number_format($value, 2);
                                            } else {
                                                echo htmlspecialchars($value ?: '-');
                                            }
                                            ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <button class="download-btn" onclick="downloadAsPDF()">Download as PDF</button>
                <?php else: ?>
                    <div class="no-results">No data found for this report</div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="js/generate_reports.js"></script>
</body>
</html>