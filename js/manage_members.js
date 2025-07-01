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

function openEditModal(memberId, firstName, lastName, nationalId, phone, email, dob, username) {
    document.getElementById('edit_member_id').value = memberId;
    document.getElementById('edit_first_name').value = firstName;
    document.getElementById('edit_last_name').value = lastName;
    document.getElementById('edit_national_id').value = nationalId;
    document.getElementById('edit_phone').value = phone;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_dob').value = dob;
    document.getElementById('edit_username').value = username;
    openModal('edit-member-modal');
}

function confirmDelete(memberId) {
    if (confirm('Are you sure you want to delete this member?')) {
        window.location.href = 'manage_members.php?delete=' + memberId;
    }
}