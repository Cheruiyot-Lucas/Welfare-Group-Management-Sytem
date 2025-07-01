function logout() {
    if (confirm('Are you sure you want to logout?')) {
        window.location.href = 'logout.php';
    }
}


function calculateLoanTerms() {
    const amount = parseFloat(document.getElementById('loanAmount').value) || 0;

    // Calculate interest rate based on amount
    let interestRate;
    if (amount <= 5000) {
        interestRate = 8.5;
    } else if (amount <= 10000) {
        interestRate = 7.5;
    } else if (amount <= 20000) {
        interestRate = 6.5;
    } else {
        interestRate = 5.5;
    }

    // Calculate interest and total repayment
    const interest = (amount * interestRate / 100).toFixed(2);
    const totalRepayment = (amount + parseFloat(interest)).toFixed(2);

    // Update display
    document.getElementById('interestRateDisplay').textContent = interestRate + '%';
    document.getElementById('totalInterestDisplay').textContent = 'KSH ' + interest;
    document.getElementById('totalRepaymentDisplay').textContent = 'KSH ' + totalRepayment;

    // Update hidden field for form submission
    document.getElementById('calculatedInterest').value = interestRate;
}

// Calculate terms when page loads (if amount is pre-filled)
document.addEventListener('DOMContentLoaded', function () {
    calculateLoanTerms();
});