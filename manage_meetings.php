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
    if (isset($_POST['add_meeting'])) {
        $meeting_id = 'MT' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        $date = $conn->real_escape_string($_POST['meeting_date']);
        $venue = $conn->real_escape_string($_POST['venue']);
        $agenda = $conn->real_escape_string($_POST['agenda'] ?? '');
        $recorded_by = $_SESSION['admin_id'];
        
        $sql = "INSERT INTO meeting (meeting_id, meeting_date, venue, agenda, recorded_by) 
                VALUES ('$meeting_id', '$date', '$venue', '$agenda', '$recorded_by')";
        
        if ($conn->query($sql)) {
            $success = "Meeting added successfully!";
        } else {
            $error = "Error adding meeting: " . $conn->error;
        }
    } elseif (isset($_POST['edit_meeting'])) {
        $meeting_id = $conn->real_escape_string($_POST['meeting_id']);
        $date = $conn->real_escape_string($_POST['meeting_date']);
        $venue = $conn->real_escape_string($_POST['venue']);
        $agenda = $conn->real_escape_string($_POST['agenda'] ?? '');
        
        $sql = "UPDATE meeting SET 
                meeting_date = '$date',
                venue = '$venue',
                agenda = '$agenda'
                WHERE meeting_id = '$meeting_id'";
        
        if ($conn->query($sql)) {
            $success = "Meeting updated successfully!";
        } else {
            $error = "Error updating meeting: " . $conn->error;
        }
    }
} elseif (isset($_GET['delete'])) {
    $meeting_id = $conn->real_escape_string($_GET['delete']);
    $conn->query("DELETE FROM meeting WHERE meeting_id = '$meeting_id'");
    $success = "Meeting deleted successfully!";
}
// Get all meetings
$meetings = $conn->query("SELECT * FROM meeting ORDER BY meeting_date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Meetings - WGMS</title>
    <link rel="stylesheet" href="css/manage_meetings.css">
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
            <li class="active"><a href="manage_meetings.php">Manage Meetings</a></li>
            <li><a href="generate_reports.php">Generate Reports</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Manage Meetings</h1>
            <button class="logout-btn" onclick="logout()">Logout</button>
        </div>

        <?php if (isset($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <button class="add-meeting-btn" onclick="openModal('add-meeting-modal')">Schedule New Meeting</button>

        <div class="meetings-container">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Venue</th>
                        <th>Agenda</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($meeting = $meetings->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo date('M j, Y', strtotime($meeting['meeting_date'])); ?></td>
                            <td><?php echo $meeting['venue']; ?></td>
                            <td><?php echo $meeting['agenda'] ?: '-'; ?></td>
                            <td>
                                <button class="action-btn edit-btn" onclick="openEditModal(
                                    '<?php echo $meeting['meeting_id']; ?>',
                                    '<?php echo $meeting['meeting_date']; ?>',
                                    '<?php echo $meeting['venue']; ?>',
                                    `<?php echo addslashes($meeting['agenda']); ?>`
                                )">Edit</button>
                                <button class="action-btn delete-btn" onclick="confirmDelete('<?php echo $meeting['meeting_id']; ?>')">Delete</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Meeting Modal -->
    <div id="add-meeting-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('add-meeting-modal')">&times;</span>
            <h2>Schedule New Meeting</h2>
            <form method="POST" action="manage_meetings.php">
                <div class="form-group">
                    <label for="meeting_date">Date</label>
                    <input type="datetime-local" id="meeting_date" name="meeting_date" required>
                </div>
                <div class="form-group">
                    <label for="venue">Venue</label>
                    <input type="text" id="venue" name="venue" required>
                </div>
                <div class="form-group">
                    <label for="agenda">Agenda</label>
                    <textarea id="agenda" name="agenda"></textarea>
                </div>
                <button type="submit" name="add_meeting" class="submit-btn">Schedule Meeting</button>
            </form>
        </div>
    </div>

    <!-- Edit Meeting Modal -->
    <div id="edit-meeting-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('edit-meeting-modal')">&times;</span>
            <h2>Edit Meeting</h2>
            <form method="POST" action="manage_meetings.php">
                <input type="hidden" id="edit_meeting_id" name="meeting_id">
                <div class="form-group">
                    <label for="edit_meeting_date">Date</label>
                    <input type="datetime-local" id="edit_meeting_date" name="meeting_date" required>
                </div>
                <div class="form-group">
                    <label for="edit_venue">Venue</label>
                    <input type="text" id="edit_venue" name="venue" required>
                </div>
                <div class="form-group">
                    <label for="edit_agenda">Agenda</label>
                    <textarea id="edit_agenda" name="agenda"></textarea>
                </div>
                <button type="submit" name="edit_meeting" class="submit-btn">Update Meeting</button>
            </form>
        </div>
    </div>

    <script src="js/manage_meetings.js"></script>
</body>
</html>