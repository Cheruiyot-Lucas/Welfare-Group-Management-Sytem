<?php
session_start();
if (!isset($_SESSION['member_id'])) {
    header("Location: index.html");
    exit();
}

include 'config.php';

// Get member details
$member_id = $_SESSION['member_id'];
$member_query = "SELECT m.*, a.balance, a.account_id FROM member m 
                 LEFT JOIN account a ON m.member_id = a.member_id 
                 WHERE m.member_id = '$member_id'";
$member_result = $conn->query($member_query);
$member = $member_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - WGMS</title>
    <link rel="stylesheet" href="css/profile.css">
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
            <li><a href="meeting_schedule.php">Meeting Schedule</a></li>
            <li class="active"><a href="profile.php">My Profile</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>My Profile</h1>
            <button class="logout-btn" onclick="logout()">Logout</button>
        </div>

        <div class="profile-container">
            <div class="profile-header">
                <div class="profile-info">
                    <div class="profile-avatar"><?php echo strtoupper(substr($member['first_name'], 0, 1)); ?></div>
                    <div class="profile-details">
                        <h2><?php echo $member['first_name'] . ' ' . $member['last_name']; ?></h2>
                        <p>Member ID: <?php echo $member['member_id']; ?></p>
                        <p>Account ID: <?php echo $member['account_id']; ?></p>
                    </div>
                </div>
                <!--<button class="edit-btn" onclick="window.location.href='edit_profile.php'">Edit Profile</button>-->
            </div>

            <div class="profile-section">
                <h3>Personal Information</h3>
                <div class="detail-row">
                    <div class="detail-label">First Name</div>
                    <div class="detail-value"><?php echo $member['first_name']; ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Last Name</div>
                    <div class="detail-value"><?php echo $member['last_name']; ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Date of Birth</div>
                    <div class="detail-value"><?php echo date('F j, Y', strtotime($member['dob'])); ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">National ID</div>
                    <div class="detail-value"><?php echo $member['national_id']; ?></div>
                </div>
            </div>

            <div class="profile-section">
                <h3>Contact Information</h3>
                <div class="detail-row">
                    <div class="detail-label">Phone Number</div>
                    <div class="detail-value"><?php echo $member['phone_number']; ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Email</div>
                    <div class="detail-value"><?php echo $member['email']; ?></div>
                </div>
            </div>

            <div class="profile-section">
                <h3>Account Information</h3>
                <div class="detail-row">
                    <div class="detail-label">Username</div>
                    <div class="detail-value"><?php echo $member['username']; ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Account Balance</div>
                    <div class="detail-value">KSH <?php echo number_format($member['balance'], 2); ?></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function logout() 
        {
         if (confirm('Are you sure you want to logout?')) 
         {
         window.location.href = 'logout.php';
          }
       }
    </script>
</body>
</html>