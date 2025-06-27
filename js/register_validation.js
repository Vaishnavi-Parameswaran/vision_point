document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById('signupForm');
  const fullname = document.getElementById('fullname');
  const nic = document.getElementById('nic');
  const email = document.getElementById('email');
  const phone = document.getElementById('phone');
  const password = document.getElementById('password');
  const confirmPassword = document.getElementById('confirm_password');

  const nameError = document.getElementById('nameError');
  const nicError = document.getElementById('nicError');
  const emailError = document.getElementById('emailError');
  const phoneError = document.getElementById('phoneError');
  const passError = document.getElementById('passError');
  const confirmError = document.getElementById('confirmError');

  const nameRegex = /^[A-Z][a-z]+\s[A-Z][a-z]+$/;
  const nicRegex = /^[0-9]{9}[vVxX]$|^[0-9]{12}$/;
  const phoneRegex = /^[0-9]{10}$/;
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  const passwordRegex = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z0-9]).{6,}$/;
  // This allows any special character, not just @$!%*?&

  // Dark mode toggle
  const darkToggle = document.getElementById('darkModeToggle');
  if (darkToggle) {
    darkToggle.addEventListener('change', () => {
      document.body.classList.toggle('dark-mode', darkToggle.checked);
    });
  }

  function showError(input, message, errorField) {
    errorField.textContent = message;
    input.style.borderColor = 'red';
  }

  function clearError(input, errorField) {
    errorField.textContent = '';
    input.style.borderColor = '';
  }

  form.addEventListener('submit', function (e) {
    let valid = true;

    if (!nameRegex.test(fullname.value.trim())) {
      showError(fullname, 'Use format: John Doe (capitalize each name)', nameError);
      valid = false;
    } else {
      clearError(fullname, nameError);
    }

    if (!nicRegex.test(nic.value.trim())) {
      showError(nic, 'NIC must be 9 digits + V/v or 12 digits', nicError);
      valid = false;
    } else {
      clearError(nic, nicError);
    }

    if (!emailRegex.test(email.value.trim())) {
      showError(email, 'Enter a valid email address', emailError);
      valid = false;
    } else {
      clearError(email, emailError);
    }

    if (!phoneRegex.test(phone.value.trim())) {
      showError(phone, 'Enter a valid 10-digit mobile number', phoneError);
      valid = false;
    } else {
      clearError(phone, phoneError);
    }

    if (!passwordRegex.test(password.value)) {
      showError(password, 'Min 6 chars, must include letter, number, symbol', passError);
      valid = false;
    } else {
      clearError(password, passError);
    }

    if (password.value !== confirmPassword.value) {
      showError(confirmPassword, 'Passwords do not match', confirmError);
      valid = false;
    } else {
      clearError(confirmPassword, confirmError);
    }

    if (!valid) {
      e.preventDefault(); // Block form submission if errors exist
    }
  });
});

