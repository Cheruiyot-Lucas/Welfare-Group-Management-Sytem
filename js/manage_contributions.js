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

function openEditModal(contributionId, memberId, amount, date, notes) {
    document.getElementById('edit_contribution_id').value = contributionId;
    document.getElementById('edit_member_id').value = memberId;
    document.getElementById('edit_amount').value = amount;
    document.getElementById('edit_contribution_date').value = date;
    document.getElementById('edit_notes').value = notes;
    openModal('edit-contribution-modal');
}

function confirmDelete(contributionId) {
    if (confirm('Are you sure you want to delete this contribution?')) {
        window.location.href = 'manage_contributions.php?delete=' + contributionId;
    }
}