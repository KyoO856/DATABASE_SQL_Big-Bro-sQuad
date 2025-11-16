const newPassword = document.getElementById('newPassword');
const confirmPassword = document.getElementById('confirmNewPassword');
const passwordError = document.getElementById('passwordError');
const passwordForm = document.getElementById('passwordForm');

function validatePassword() {
    if (confirmPassword.value !== '' && newPassword.value !== confirmPassword.value) {
        passwordError.style.display = 'block';
        confirmPassword.style.borderColor = '#e74c3c';
        return false;
    } else {
        passwordError.style.display = 'none';
        confirmPassword.style.borderColor = '#ddd';
        return true;
    }
}

if (confirmPassword) {
    confirmPassword.addEventListener('input', validatePassword);
}

if (newPassword) {
    newPassword.addEventListener('input', function() {
        if (confirmPassword.value !== '') {
            validatePassword();
        }
    });
}

// Prevent form submission if passwords don't match
if (passwordForm) {
    passwordForm.addEventListener('submit', function(e) {
        if (!validatePassword()) {
            e.preventDefault();
            alert('Please make sure passwords match');
        }
    });
}

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.3s';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    });
});