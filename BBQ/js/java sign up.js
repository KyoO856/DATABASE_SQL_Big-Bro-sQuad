const password = document.getElementById('password');
const confirmPassword = document.getElementById('confirmPassword');
const passwordError = document.getElementById('passwordError');
const phoneInput = document.getElementById('phone');
const phoneError = document.getElementById('phoneError');

// check password match
function validatePassword() {
    if (confirmPassword.value !== '' && password.value !== confirmPassword.value) {
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

if (password) {
    password.addEventListener('input', function() {
        if (confirmPassword.value !== '') {
            validatePassword();
        }
    });
}

// checkphone
if (phoneInput) {
    phoneInput.addEventListener('input', function() {
        if (this.value.length > 0 && !this.validity.valid) {
            phoneError.style.display = 'block';
            this.style.borderColor = '#e74c3c';
        } else {
            phoneError.style.display = 'none';
            this.style.borderColor = '#ddd';
        }
    });
}