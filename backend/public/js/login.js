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
      var err = document.getElementById('login-error');
      if (err) err.style.display = 'none';
    });
    passwordInput.addEventListener('input', function () {
      clearInvalid(passwordInput);
      var err = document.getElementById('password-error');
      if (err) err.style.display = 'none';
    });

    form.addEventListener('submit', function (event) {
      var loginEmpty = loginInput.value.trim() === '';
      var passwordEmpty = passwordInput.value === '';

      clearInvalid(loginInput);
      clearInvalid(passwordInput);

      var hasError = false;
      var loginErr = document.getElementById('login-error');
      var passErr = document.getElementById('password-error');

      if (loginEmpty) {
        hasError = true;
        loginInput.setAttribute('aria-invalid', 'true');
        if (loginErr) {
          loginErr.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg><span>Username or Email is required.</span>';
          loginErr.style.display = 'flex';
        }
      } else if (loginErr) {
        loginErr.style.display = 'none';
      }

      if (passwordEmpty) {
        hasError = true;
        passwordInput.setAttribute('aria-invalid', 'true');
        if (passErr) {
          passErr.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg><span>Password is required.</span>';
          passErr.style.display = 'flex';
        }
      } else if (passErr) {
        passErr.style.display = 'none';
      }

      if (hasError) {
        event.preventDefault();
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
