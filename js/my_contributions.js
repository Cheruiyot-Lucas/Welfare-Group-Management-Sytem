function logout() {
    if (confirm('Are you sure you want to logout?')) {
        window.location.href = 'logout.php';
    }
}

function toggleContributionForm() {
    const form = document.getElementById('contribution-form');
    form.classList.toggle('hidden');
}