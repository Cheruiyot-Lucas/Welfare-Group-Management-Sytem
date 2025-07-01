function logout() {
    if (confirm('Are you sure you want to logout?')) {
        window.location.href = 'logout.php';
    }
}

function showMeetings(type) {
    // Hide all meeting lists
    document.querySelectorAll('.meeting-list').forEach(list => {
        list.classList.remove('active');
    });

    // Deactivate all tabs
    document.querySelectorAll('.meeting-tab').forEach(tab => {
        tab.classList.remove('active');
    });

    // Activate selected tab
    event.target.classList.add('active');

    // Show selected meeting list
    document.getElementById(type + '-meetings').classList.add('active');
}