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
    if (isset($_POST['add_member'])) {
        $member_id = 'M' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        $first_name = $conn->real_escape_string($_POST['first_name']);
        $last_name = $conn->real_escape_string($_POST['last_name']);
        $national_id = $conn->real_escape_string($_POST['national_id']);
        $phone = $conn->real_escape_string($_POST['phone']);
        $email = $conn->real_escape_string($_POST['email']);
        $dob = $conn->real_escape_string($_POST['dob']);
        $username = $conn->real_escape_string($_POST['username']);
        $password = password_hash($conn->real_escape_string($_POST['password']), PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO member (member_id, first_name, last_name, national_id, phone_number, email, dob, username, password) 
                VALUES ('$member_id', '$first_name', '$last_name', '$national_id', '$phone', '$email', '$dob', '$username', '$password')";
        
        if ($conn->query($sql)) {
            // Create account for the member
            $account_id = 'ACC' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
            $conn->query("INSERT INTO account (account_id, member_id, balance) VALUES ('$account_id', '$member_id', 0)");
            $success = "Member added successfully!";
        } else {
            $error = "Error adding member: " . $conn->error;
        }
    } elseif (isset($_POST['edit_member'])) {
        $member_id = $conn->real_escape_string($_POST['member_id']);
        $first_name = $conn->real_escape_string($_POST['first_name']);
        $last_name = $conn->real_escape_string($_POST['last_name']);
        $national_id = $conn->real_escape_string($_POST['national_id']);
        $phone = $conn->real_escape_string($_POST['phone']);
        $email = $conn->real_escape_string($_POST['email']);
        $dob = $conn->real_escape_string($_POST['dob']);
        $username = $conn->real_escape_string($_POST['username']);
        
        $sql = "UPDATE member SET 
                first_name = '$first_name',
                last_name = '$last_name',
                national_id = '$national_id',
                phone_number = '$phone',
                email = '$email',
                dob = '$dob',
                username = '$username'
                WHERE member_id = '$member_id'";
        
        if ($conn->query($sql)) {
            $success = "Member updated successfully!";
        } else {
            $error = "Error updating member: " . $conn->error;
        }
    }
} elseif (isset($_GET['delete'])) {
    $member_id = $conn->real_escape_string($_GET['delete']);
    $conn->query("DELETE FROM member WHERE member_id = '$member_id'");
    $conn->query("DELETE FROM account WHERE member_id = '$member_id'");
    $success = "Member deleted successfully!";
}

// Get all members
$members = $conn->query("SELECT m.*, a.balance FROM member m LEFT JOIN account a ON m.member_id = a.member_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Members - WGMS</title>
    <link rel="stylesheet" href="css/manage_members.css">
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>WGMS Admin</h2>
             <p>Welcome, <?php echo $admin['username']; ?></p>
        </div>
        <ul class="sidebar-menu">
            <li><a href="admin_dashboard.php">Dashboard</a></li>
            <li class="active"><a href="manage_members.php">Manage Members</a></li>
            <li><a href="manage_contributions.php">Manage Contributions</a></li>
            <li><a href="manage_meetings.php">Manage Meetings</a></li>
            <li><a href="generate_reports.php">Generate Reports</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Manage Members</h1>
            <button class="logout-btn" onclick="logout()">Logout</button>
        </div>

        <?php if (isset($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <button class="add-member-btn" onclick="openModal('add-member-modal')">Add New Member</button>

        <div class="members-container">
            <table>
                <thead>
                    <tr>
                        <th>Member ID</th>
                        <th>Name</th>
                        <th>National ID</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Balance</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($member = $members->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $member['member_id']; ?></td>
                            <td><?php echo $member['first_name'] . ' ' . $member['last_name']; ?></td>
                            <td><?php echo $member['national_id']; ?></td>
                            <td><?php echo $member['phone_number']; ?></td>
                            <td><?php echo $member['email']; ?></td>
                            <td>KSH <?php echo number_format($member['balance'], 2); ?></td>
                            <td>
                                <button class="action-btn edit-btn" onclick="openEditModal(
                                    '<?php echo $member['member_id']; ?>',
                                    '<?php echo $member['first_name']; ?>',
                                    '<?php echo $member['last_name']; ?>',
                                    '<?php echo $member['national_id']; ?>',
                                    '<?php echo $member['phone_number']; ?>',
                                    '<?php echo $member['email']; ?>',
                                    '<?php echo $member['dob']; ?>',
                                    '<?php echo $member['username']; ?>'
                                )">Edit</button>
                                <button class="action-btn delete-btn" onclick="confirmDelete('<?php echo $member['member_id']; ?>')">Delete</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Member Modal -->
    <div id="add-member-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('add-member-modal')">&times;</span>
            <h2>Add New Member</h2>
            <form method="POST" action="manage_members.php">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" required>
                </div>
                <div class="form-group">
                    <label for="national_id">National ID</label>
                    <input type="text" id="national_id" name="national_id" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="text" id="phone" name="phone" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="dob">Date of Birth</label>
                    <input type="date" id="dob" name="dob" required>
                </div>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" name="add_member" class="submit-btn">Add Member</button>
            </form>
        </div>
    </div>

    <!-- Edit Member Modal -->
    <div id="edit-member-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('edit-member-modal')">&times;</span>
            <h2>Edit Member</h2>
            <form method="POST" action="manage_members.php">
                <input type="hidden" id="edit_member_id" name="member_id">
                <div class="form-group">
                    <label for="edit_first_name">First Name</label>
                    <input type="text" id="edit_first_name" name="first_name" required>
                </div>
                <div class="form-group">
                    <label for="edit_last_name">Last Name</label>
                    <input type="text" id="edit_last_name" name="last_name" required>
                </div>
                <div class="form-group">
                    <label for="edit_national_id">National ID</label>
                    <input type="text" id="edit_national_id" name="national_id" required>
                </div>
                <div class="form-group">
                    <label for="edit_phone">Phone Number</label>
                    <input type="text" id="edit_phone" name="phone" required>
                </div>
                <div class="form-group">
                    <label for="edit_email">Email</label>
                    <input type="email" id="edit_email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="edit_dob">Date of Birth</label>
                    <input type="date" id="edit_dob" name="dob" required>
                </div>
                <div class="form-group">
                    <label for="edit_username">Username</label>
                    <input type="text" id="edit_username" name="username" required>
                </div>
                <button type="submit" name="edit_member" class="submit-btn">Update Member</button>
            </form>
        </div>
    </div>

    <script src="js/manage_members.js"></script>
</body>
</html>