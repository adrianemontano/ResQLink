(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    var form = document.querySelector('.rq-form');
    var submitBtn = document.getElementById('login-submit');
    var loginInput = document.getElementById('login');
    var passwordInput = document.getElementById('password');

    if (!form || !submitBtn || !loginInput || !passwordInput) {
      return;
    }

    function clearInvalid(input) {
      input.removeAttribute('aria-invalid');
    }

    loginInput.addEventListener('input', function () {
      clearInvalid(loginInput);
    });
    passwordInput.addEventListener('input', function () {
      clearInvalid(passwordInput);
    });

    form.addEventListener('submit', function (event) {
      var loginEmpty = loginInput.value.trim() === '';
      var passwordEmpty = passwordInput.value === '';

      clearInvalid(loginInput);
      clearInvalid(passwordInput);

      if (loginEmpty || passwordEmpty) {
        event.preventDefault();

        if (loginEmpty) {
          loginInput.setAttribute('aria-invalid', 'true');
        }
        if (passwordEmpty) {
          passwordInput.setAttribute('aria-invalid', 'true');
        }

        var message;
        if (loginEmpty && passwordEmpty) {
          message = 'Please enter your username or email and password.';
        } else if (loginEmpty) {
          message = 'Please enter your username or email.';
        } else {
          message = 'Please enter your password.';
        }

        if (typeof window.showToast === 'function') {
          window.showToast({ type: 'error', message: message });
        }

        (loginEmpty ? loginInput : passwordInput).focus();
        return;
      }

      var btnText = submitBtn.querySelector('.btn-text');
      var spinner = submitBtn.querySelector('.rq-spinner');
      if (btnText && spinner) {
        btnText.textContent = 'Signing in...';
        spinner.style.display = 'inline-block';
        submitBtn.classList.add('is-loading');
        submitBtn.disabled = true;
      }
    });
  });
})();
