function logout() {
    if (confirm('Are you sure you want to logout?')) {
        window.location.href = 'logout.php';
    }
}

function openModal(modalId) {
    document.getElementById(modalId).style.display = 'block';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function openEditModal(meetingId, meetingDate, venue, agenda) {
    document.getElementById('edit_meeting_id').value = meetingId;
    document.getElementById('edit_meeting_date').value = meetingDate.replace(' ', 'T');
    document.getElementById('edit_venue').value = venue;
    document.getElementById('edit_agenda').value = agenda;
    openModal('edit-meeting-modal');
}

function confirmDelete(meetingId) {
    if (confirm('Are you sure you want to delete this meeting?')) {
        window.location.href = 'manage_meetings.php?delete=' + meetingId;
    }
}