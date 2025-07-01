document.getElementById('adminSignupForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;

    if (password !== confirmPassword) {
        document.getElementById('errorMessage').textContent = 'Passwords do not match';
        return;
    }

    const formData = {
        accessCode: document.getElementById('accessCode').value,
        username: document.getElementById('username').value,
        password: password
    };

    fetch('admin_signup.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData)
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Admin registration successful! Please login.');
                window.location.href = 'index.html';
            } else {
                document.getElementById('errorMessage').textContent = data.message;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('errorMessage').textContent = 'Registration failed. Please try again.';
        });
});