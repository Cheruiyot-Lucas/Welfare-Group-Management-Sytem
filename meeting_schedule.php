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

// Get all meetings
$meetings_query = "SELECT * FROM meeting ORDER BY meeting_date DESC";
$meetings = $conn->query($meetings_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meeting Schedule - WGMS</title>
    <link rel="stylesheet" href="css/meeting_schedule.css">
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>WGMS Member</h2>
            <p>Welcome, <?php echo $member['first_name']; ?></p>
        </div>
        <ul class="sidebar-menu">
            <li><a href="member_dashboard.php">Dashboard</a></li>
            <li><a href="my_contributions.php">My Contributions</a></li>
            <li class="active"><a href="meeting_schedule.php">Meeting Schedule</a></li>
            <li><a href="profile.php">My Profile</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Meeting Schedule</h1>
            <button class="logout-btn" onclick="logout()">Logout</button>
        </div>

        <div class="meetings-container">
            <div class="meeting-tabs">
                <div class="meeting-tab active" onclick="showMeetings('upcoming')">Upcoming Meetings</div>
                <div class="meeting-tab" onclick="showMeetings('past')">Past Meetings</div>
                <div class="meeting-tab" onclick="showMeetings('all')">All Meetings</div>
            </div>

            <div id="upcoming-meetings" class="meeting-list active">
                <h2>Upcoming Meetings</h2>
                <?php 
                $upcoming_query = "SELECT * FROM meeting WHERE meeting_date >= CURDATE() ORDER BY meeting_date ASC";
                $upcoming_meetings = $conn->query($upcoming_query);
                
                if ($upcoming_meetings->num_rows > 0): ?>
                    <?php while($meeting = $upcoming_meetings->fetch_assoc()): ?>
                        <div class="meeting-item upcoming">
                            <div class="meeting-date"><?php echo date('F j, Y', strtotime($meeting['meeting_date'])); ?></div>
                            <div><strong>Venue:</strong> <?php echo $meeting['venue']; ?></div>
                            <div><strong>Agenda:</strong> <?php echo $meeting['agenda'] ? $meeting['agenda'] : 'Not specified'; ?></div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No upcoming meetings scheduled.</p>
                <?php endif; ?>
            </div>

            <div id="past-meetings" class="meeting-list">
                <h2>Past Meetings</h2>
                <?php 
                $past_query = "SELECT * FROM meeting WHERE meeting_date < CURDATE() ORDER BY meeting_date DESC";
                $past_meetings = $conn->query($past_query);
                
                if ($past_meetings->num_rows > 0): ?>
                    <?php while($meeting = $past_meetings->fetch_assoc()): ?>
                        <div class="meeting-item past">
                            <div class="meeting-date"><?php echo date('F j, Y', strtotime($meeting['meeting_date'])); ?></div>
                            <div><strong>Venue:</strong> <?php echo $meeting['venue']; ?></div>
                            <div><strong>Agenda:</strong> <?php echo $meeting['agenda'] ? $meeting['agenda'] : 'Not specified'; ?></div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No past meetings found.</p>
                <?php endif; ?>
            </div>

            <div id="all-meetings" class="meeting-list">
                <h2>All Meetings</h2>
                <?php if ($meetings->num_rows > 0): ?>
                    <?php while($meeting = $meetings->fetch_assoc()): ?>
                        <div class="meeting-item <?php echo $meeting['meeting_date'] >= date('Y-m-d') ? 'upcoming' : 'past'; ?>">
                            <div class="meeting-date"><?php echo date('F j, Y', strtotime($meeting['meeting_date'])); ?></div>
                            <div><strong>Venue:</strong> <?php echo $meeting['venue']; ?></div>
                            <div><strong>Agenda:</strong> <?php echo $meeting['agenda'] ? $meeting['agenda'] : 'Not specified'; ?></div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No meetings found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="js/meeting_schedule.js"></script>
</body>
</html>