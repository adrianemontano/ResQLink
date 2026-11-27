/**
 * ResQLink Form Validation & Change Detection
 * - Disables native browser tooltips (novalidate)
 * - Renders custom inline errors with red border and exclamation icon
 * - Detects unchanged edit/profile forms ('Nothing to update')
 */
(function () {
  'use strict';

  function createErrorIcon() {
    var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.setAttribute('width', '14');
    svg.setAttribute('height', '14');
    svg.setAttribute('viewBox', '0 0 24 24');
    svg.setAttribute('fill', 'none');
    svg.setAttribute('stroke', 'currentColor');
    svg.setAttribute('stroke-width', '2');
    svg.setAttribute('stroke-linecap', 'round');
    svg.setAttribute('stroke-linejoin', 'round');

    var circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
    circle.setAttribute('cx', '12');
    circle.setAttribute('cy', '12');
    circle.setAttribute('r', '10');

    var line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
    line.setAttribute('x1', '12');
    line.setAttribute('y1', '8');
    line.setAttribute('x2', '12');
    line.setAttribute('y2', '12');

    var dot = document.createElementNS('http://www.w3.org/2000/svg', 'line');
    dot.setAttribute('x1', '12');
    dot.setAttribute('y1', '16');
    dot.setAttribute('x2', '12.01');
    dot.setAttribute('y2', '16');

    svg.appendChild(circle);
    svg.appendChild(line);
    svg.appendChild(dot);
    return svg;
  }

  function getFieldLabel(input) {
    if (input.getAttribute('aria-label')) {
      return input.getAttribute('aria-label');
    }
    var id = input.id;
    if (id) {
      var labelEl = document.querySelector('label[for="' + id + '"]');
      if (labelEl) {
        var clone = labelEl.cloneNode(true);
        var asterisk = clone.querySelector('.required-asterisk');
        if (asterisk) asterisk.remove();
        var txt = clone.textContent.trim().replace(/[:*]+$/, '').trim();
        if (txt) return txt;
      }
    }
    var parentLabel = input.closest('label');
    if (parentLabel) {
      var pClone = parentLabel.cloneNode(true);
      var pInputs = pClone.querySelectorAll('input, select, textarea, button, small, .error-text, .rq-field-error');
      pInputs.forEach(function (el) { el.remove(); });
      var pText = pClone.textContent.trim().replace(/[:*]+$/, '').trim();
      if (pText) return pText;
    }
    if (input.placeholder) {
      return input.placeholder.trim();
    }
    if (input.name) {
      var name = input.name.replace(/\[.*\]$/, '').replace(/_/g, ' ');
      return name.charAt(0).toUpperCase() + name.slice(1);
    }
    return 'This field';
  }

  function getFormValues(form) {
    var values = {};
    var elements = form.querySelectorAll('input, select, textarea');
    elements.forEach(function (el) {
      if (!el.name || el.type === 'submit' || el.type === 'button') return;
      if (el.name === '_token' || el.name === '_method') return;

      if (el.type === 'checkbox') {
        values[el.name] = el.checked ? (el.value || '1') : '';
      } else if (el.type === 'radio') {
        if (el.checked) values[el.name] = el.value;
      } else {
        values[el.name] = el.value !== undefined ? el.value.trim() : '';
      }
    });
    return values;
  }

  function isUpdateForm(form) {
    if (form.classList.contains('filter-row') || form.getAttribute('action') === '/logout') {
      return false;
    }
    var methodInput = form.querySelector('input[name="_method"]');
    if (methodInput) {
      var method = methodInput.value.toUpperCase();
      if (method === 'PUT' || method === 'PATCH') return true;
    }
    var action = (form.getAttribute('action') || '').toLowerCase();
    if (action.includes('update') || action.includes('edit') || action.includes('password') || action.includes('account')) {
      return true;
    }
    return form.closest('.account-card') !== null;
  }

  function showInlineError(input, message) {
    input.setAttribute('aria-invalid', 'true');
    var container = input.parentElement;
    var existing = container.querySelector('.rq-field-error');
    if (!existing) {
      existing = document.createElement('div');
      existing.className = 'rq-field-error';
      existing.setAttribute('role', 'alert');
      container.appendChild(existing);
    }
    existing.innerHTML = '';
    existing.appendChild(createErrorIcon());
    var span = document.createElement('span');
    span.textContent = message;
    existing.appendChild(span);
    existing.style.display = 'flex';
  }

  function clearInlineError(input) {
    input.removeAttribute('aria-invalid');
    var container = input.parentElement;
    var existing = container.querySelector('.rq-field-error');
    if (existing) {
      existing.style.display = 'none';
    }
    var small = container.querySelector('small');
    if (small && small.parentElement === container) {
      small.style.display = 'none';
    }
  }

  function setupForm(form) {
    form.setAttribute('novalidate', 'novalidate');

    if (isUpdateForm(form)) {
      form._initialValues = getFormValues(form);
    }

    var fields = form.querySelectorAll('input, select, textarea');
    fields.forEach(function (field) {
      field.addEventListener('input', function () {
        clearInlineError(field);
      });
      field.addEventListener('change', function () {
        clearInlineError(field);
      });
    });

    form.addEventListener('submit', function (event) {
      var hasErrors = false;
      var firstInvalid = null;

      fields.forEach(function (field) {
        if (field.disabled || field.type === 'hidden' || field.type === 'submit' || field.type === 'button') {
          return;
        }

        var isRequired = field.hasAttribute('required');
        var val = field.value !== undefined ? field.value.trim() : '';

        // 1. Required Check
        if (isRequired && val === '') {
          hasErrors = true;
          showInlineError(field, getFieldLabel(field) + ' is required.');
          if (!firstInvalid) firstInvalid = field;
          return;
        }

        // 2. Email format
        if (field.type === 'email' && val !== '') {
          var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          if (!emailRegex.test(val)) {
            hasErrors = true;
            showInlineError(field, 'Please enter a valid email address.');
            if (!firstInvalid) firstInvalid = field;
            return;
          }
        }

        // 3. Minlength
        var minLen = field.getAttribute('minlength');
        if (minLen && val !== '' && val.length < parseInt(minLen, 10)) {
          hasErrors = true;
          showInlineError(field, getFieldLabel(field) + ' must be at least ' + minLen + ' characters.');
          if (!firstInvalid) firstInvalid = field;
          return;
        }

        // 4. Password confirmation match
        if (field.name === 'password_confirmation') {
          var pw = form.querySelector('input[name="password"]');
          if (pw && field.value !== pw.value) {
            hasErrors = true;
            showInlineError(field, 'Passwords do not match.');
            if (!firstInvalid) firstInvalid = field;
            return;
          }
        }
      });

      if (hasErrors) {
        event.preventDefault();
        event.stopImmediatePropagation();
        if (firstInvalid) firstInvalid.focus();
        return false;
      }

      // Check 'Nothing to update' on edit/profile forms
      if (form._initialValues) {
        var currentValues = getFormValues(form);
        var changed = false;
        var keys = Object.keys(currentValues);
        for (var i = 0; i < keys.length; i++) {
          var k = keys[i];
          if (currentValues[k] !== form._initialValues[k]) {
            changed = true;
            break;
          }
        }

        if (!changed) {
          event.preventDefault();
          event.stopImmediatePropagation();
          if (typeof window.showToast === 'function') {
            window.showToast({
              type: 'info',
              message: 'No changes detected. Nothing to update.'
            });
          }
          return false;
        }
      }
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('form').forEach(setupForm);
  });
})();
