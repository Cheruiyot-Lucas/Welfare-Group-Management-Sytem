document.addEventListener('DOMContentLoaded', function () {
    const menuLoginBtn = document.getElementById('menuLoginBtn');
    const userOptions = document.getElementById('userOptions');
    const loginFormsContainer = document.getElementById('loginFormsContainer');
    const memberOption = document.getElementById('memberOption');
    const adminOption = document.getElementById('adminOption');

    // Member login form template
    const memberLoginHTML = `
                <div class="login-box">
                    <h2>Member Login</h2>
                    <form id="memberLoginForm">
                        <input type="text" id="memberUsername" placeholder="Username" required>
                        <input type="password" id="memberPassword" placeholder="Password" required>
                        <button type="submit">Login</button>
                        <a href="member_signup.html" class="signup-link">Not a member? Sign up</a>
                        <div id="memberLoginError" class="error"></div>
                    </form>
                </div>
            `;

    // Admin login form template
    const adminLoginHTML = `
                <div class="login-box">
                    <h2>Admin Login</h2>
                    <form id="adminLoginForm">
                        <input type="text" id="adminUsername" placeholder="Username" required>
                        <input type="password" id="adminPassword" placeholder="Password" required>
                        <button type="submit">Login</button>
                        <a href="admin_signup.html" class="signup-link">Admin Registration</a>
                        <div id="adminLoginError" class="error"></div>
                    </form>
                </div>
            `;

    // Toggle user options when menu login button is clicked
    menuLoginBtn.addEventListener('click', function () {
        if (userOptions.style.display === 'flex') {
            userOptions.style.display = 'none';
            loginFormsContainer.style.display = 'none';
        } else {
            userOptions.style.display = 'flex';
        }
    });

    // Show member login form
    memberOption.addEventListener('click', function () {
        loginFormsContainer.innerHTML = memberLoginHTML;
        loginFormsContainer.style.display = 'flex';

        // Handle member login form submission
        document.getElementById('memberLoginForm')?.addEventListener('submit', function (e) {
            e.preventDefault();
            const username = document.getElementById('memberUsername').value;
            const password = document.getElementById('memberPassword').value;

            fetch('member_login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = 'member_dashboard.php';
                    } else {
                        document.getElementById('memberLoginError').textContent = data.message;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('memberLoginError').textContent = 'Login failed. Please try again.';
                });
        });
    });

    // Show admin login form
    adminOption.addEventListener('click', function () {
        loginFormsContainer.innerHTML = adminLoginHTML;
        loginFormsContainer.style.display = 'flex';

        // Handle admin login form submission
        document.getElementById('adminLoginForm')?.addEventListener('submit', function (e) {
            e.preventDefault();
            const username = document.getElementById('adminUsername').value;
            const password = document.getElementById('adminPassword').value;

            fetch('admin_login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = 'admin_dashboard.php';
                    } else {
                        document.getElementById('adminLoginError').textContent = data.message;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('adminLoginError').textContent = 'Login failed. Please try again.';
                });
        });
    });
});