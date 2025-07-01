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


// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_contribution'])) {
        $contribution_id = 'C' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        $member_id = $conn->real_escape_string($_POST['member_id']);
        $amount = floatval($_POST['amount']);
        $date = $conn->real_escape_string($_POST['contribution_date']);
        $notes = $conn->real_escape_string($_POST['notes'] ?? '');
        
        $conn->begin_transaction();
        try {
            // Insert contribution
            $sql = "INSERT INTO contribution (contribution_id, member_id, contribution_date, amount, notes) 
                    VALUES ('$contribution_id', '$member_id', '$date', $amount, '$notes')";
            $conn->query($sql);
            
            // Update account balance
            $conn->query("UPDATE account SET balance = balance + $amount WHERE member_id = '$member_id'");
            
            $conn->commit();
            $success = "Contribution added successfully!";
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Error adding contribution: " . $e->getMessage();
        }
    } elseif (isset($_POST['edit_contribution'])) {
        $contribution_id = $conn->real_escape_string($_POST['contribution_id']);
        $member_id = $conn->real_escape_string($_POST['member_id']);
        $amount = floatval($_POST['amount']);
        $date = $conn->real_escape_string($_POST['contribution_date']);
        $notes = $conn->real_escape_string($_POST['notes'] ?? '');
        
        $conn->begin_transaction();
        try {
            // Get old amount
            $old_amount = $conn->query("SELECT amount FROM contribution WHERE contribution_id = '$contribution_id'")->fetch_assoc()['amount'];
            $diff = $amount - $old_amount;
            
            // Update contribution
            $sql = "UPDATE contribution SET 
                    member_id = '$member_id',
                    contribution_date = '$date',
                    amount = $amount,
                    notes = '$notes'
                    WHERE contribution_id = '$contribution_id'";
            $conn->query($sql);
            
            // Update account balance
            $conn->query("UPDATE account SET balance = balance + $diff WHERE member_id = '$member_id'");
            
            $conn->commit();
            $success = "Contribution updated successfully!";
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Error updating contribution: " . $e->getMessage();
        }
    }
} elseif (isset($_GET['delete'])) {
    $contribution_id = $conn->real_escape_string($_GET['delete']);
    
    $conn->begin_transaction();
    try {
        // Get contribution details
        $contribution = $conn->query("SELECT * FROM contribution WHERE contribution_id = '$contribution_id'")->fetch_assoc();
        
        // Delete contribution
        $conn->query("DELETE FROM contribution WHERE contribution_id = '$contribution_id'");
        
        // Update account balance
        $conn->query("UPDATE account SET balance = balance - {$contribution['amount']} WHERE member_id = '{$contribution['member_id']}'");
        
        $conn->commit();
        $success = "Contribution deleted successfully!";
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Error deleting contribution: " . $e->getMessage();
    }
}

// Get all contributions with member names
$contributions = $conn->query("
    SELECT c.*, m.first_name, m.last_name 
    FROM contribution c
    JOIN member m ON c.member_id = m.member_id
    ORDER BY c.contribution_date DESC
");

// Get all members for dropdown
$members = $conn->query("SELECT member_id, first_name, last_name FROM member");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Contributions - WGMS</title>
    <link rel="stylesheet" href="css/manage_contribution.css">
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
            <li class="active"><a href="manage_contributions.php">Manage Contributions</a></li>
            <li><a href="manage_meetings.php">Manage Meetings</a></li>
            <li><a href="generate_reports.php">Generate Reports</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Manage Contributions</h1>
            <button class="logout-btn" onclick="logout()">Logout</button>
        </div>

        <?php if (isset($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <button class="add-contribution-btn" onclick="openModal('add-contribution-modal')">Add New Contribution</button>

        <div class="contributions-container">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Member</th>
                        <th>Amount</th>
                        <th>Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($contribution = $contributions->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo date('M j, Y', strtotime($contribution['contribution_date'])); ?></td>
                            <td><?php echo $contribution['first_name'] . ' ' . $contribution['last_name']; ?></td>
                            <td>KSH <?php echo number_format($contribution['amount'], 2); ?></td>
                            <td><?php echo $contribution['notes'] ?: '-'; ?></td>
                            <td>
                                <button class="action-btn edit-btn" onclick="openEditModal(
                                    '<?php echo $contribution['contribution_id']; ?>',
                                    '<?php echo $contribution['member_id']; ?>',
                                    '<?php echo $contribution['amount']; ?>',
                                    '<?php echo $contribution['contribution_date']; ?>',
                                    `<?php echo addslashes($contribution['notes']); ?>`
                                )">Edit</button>
                                <button class="action-btn delete-btn" onclick="confirmDelete('<?php echo $contribution['contribution_id']; ?>')">Delete</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Contribution Modal -->
    <div id="add-contribution-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('add-contribution-modal')">&times;</span>
            <h2>Add New Contribution</h2>
            <form method="POST" action="manage_contributions.php">
                <div class="form-group">
                    <label for="member_id">Member</label>
                    <select id="member_id" name="member_id" required>
                        <?php 
                        $members = $conn->query("SELECT member_id, first_name, last_name FROM member");
                        while($member = $members->fetch_assoc()): ?>
                            <option value="<?php echo $member['member_id']; ?>">
                                <?php echo $member['first_name'] . ' ' . $member['last_name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="amount">Amount (KSH)</label>
                    <input type="number" id="amount" name="amount" min="0.01" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="contribution_date">Date</label>
                    <input type="date" id="contribution_date" name="contribution_date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes"></textarea>
                </div>
                <button type="submit" name="add_contribution" class="submit-btn">Add Contribution</button>
            </form>
        </div>
    </div>

    <!--Contribution Modal -->
    <div id="edit-contribution-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('edit-contribution-modal')">&times;</span>
            <h2>Edit Contribution</h2>
            <form method="POST" action="manage_contributions.php">
                <input type="hidden" id="edit_contribution_id" name="contribution_id">
                <div class="form-group">
                    <label for="edit_member_id">Member</label>
                    <select id="edit_member_id" name="member_id" required>
                        <?php 
                        $members = $conn->query("SELECT member_id, first_name, last_name FROM member");
                        while($member = $members->fetch_assoc()): ?>
                            <option value="<?php echo $member['member_id']; ?>">
                                <?php echo $member['first_name'] . ' ' . $member['last_name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit_amount">Amount (KSH)</label>
                    <input type="number" id="edit_amount" name="amount" min="0.01" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="edit_contribution_date">Date</label>
                    <input type="date" id="edit_contribution_date" name="contribution_date" required>
                </div>
                <div class="form-group">
                    <label for="edit_notes">Notes</label>
                    <textarea id="edit_notes" name="notes"></textarea>
                </div>
                <button type="submit" name="edit_contribution" class="submit-btn">Update Contribution</button>
            </form>
        </div>
    </div>

    <script src="js/manage_contributions.js"></script>
</body>
</html>