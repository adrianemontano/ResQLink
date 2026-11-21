(function() {
  'use strict';

  var toastWrap = null;
  var maxToasts = 3;
  var timers = new Map();

  function getWrap() {
    if (!toastWrap) {
      toastWrap = document.getElementById('rqToasts');
    }
    return toastWrap;
  }

  function createIcon(type) {
    var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.setAttribute('width', '16');
    svg.setAttribute('height', '16');
    svg.setAttribute('viewBox', '0 0 24 24');
    svg.setAttribute('fill', 'none');
    svg.setAttribute('stroke', 'white');
    svg.setAttribute('stroke-width', '3');
    svg.setAttribute('stroke-linecap', 'round');
    svg.setAttribute('stroke-linejoin', 'round');

    var path;
    if (type === 'success') {
      path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
      path.setAttribute('d', 'M5 12l5 5L19 7');
    } else if (type === 'error') {
      path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
      path.setAttribute('d', 'M6 6l12 12M18 6L6 18');
    } else if (type === 'warning') {
      var line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
      line.setAttribute('x1', '12');
      line.setAttribute('y1', '9');
      line.setAttribute('x2', '12');
      line.setAttribute('y2', '17');
      var dot = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
      dot.setAttribute('cx', '12');
      dot.setAttribute('cy', '21');
      dot.setAttribute('r', '1');
      svg.appendChild(line);
      svg.appendChild(dot);
      return svg;
    } else if (type === 'info') {
      var circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
      circle.setAttribute('cx', '12');
      circle.setAttribute('cy', '12');
      circle.setAttribute('r', '10');
      var line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
      line.setAttribute('x1', '12');
      line.setAttribute('y1', '16');
      line.setAttribute('x2', '12');
      line.setAttribute('y2', '12');
      var dot2 = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
      dot2.setAttribute('cx', '12');
      dot2.setAttribute('cy', '8');
      dot2.setAttribute('r', '1');
      svg.appendChild(circle);
      svg.appendChild(line);
      svg.appendChild(dot2);
      return svg;
    }

    if (path) {
      svg.appendChild(path);
    }
    return svg;
  }

  function getDuration(type) {
    if (type === 'success' || type === 'info') return 4000;
    if (type === 'warning') return 6000;
    return 0;
  }

  function dismiss(toast) {
    toast.classList.add('out');
    var timer = timers.get(toast);
    if (timer) {
      clearTimeout(timer);
      timers.delete(toast);
    }
    setTimeout(function() {
      if (toast.parentNode) {
        toast.parentNode.removeChild(toast);
      }
    }, 200);
  }

  function showToast(options) {
    var wrap = getWrap();
    if (!wrap) {
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
          showToast(options);
        });
      }
      return;
    }

    var type = options.type || 'info';
    var message = options.message || '';
    var detail = options.detail || '';

    var toast = document.createElement('div');
    toast.className = 'rq-toast ' + type;
    toast.setAttribute('role', type === 'success' || type === 'info' ? 'status' : 'alert');

    var iconWrap = document.createElement('div');
    iconWrap.className = 'ic';
    iconWrap.appendChild(createIcon(type));

    var textWrap = document.createElement('div');
    textWrap.className = 'txt';
    var msgSpan = document.createElement('span');
    msgSpan.textContent = message;
    textWrap.appendChild(msgSpan);

    if (detail) {
      var detailSpan = document.createElement('span');
      detailSpan.className = 'detail';
      detailSpan.textContent = detail;
      textWrap.appendChild(detailSpan);
    }

    var closeBtn = document.createElement('button');
    closeBtn.className = 'x';
    closeBtn.setAttribute('aria-label', 'Close');
    closeBtn.innerHTML = '&times;';
    closeBtn.addEventListener('click', function() {
      dismiss(toast);
    });

    toast.appendChild(iconWrap);
    toast.appendChild(textWrap);
    toast.appendChild(closeBtn);

    wrap.insertBefore(toast, wrap.firstChild);

    var toasts = wrap.querySelectorAll('.rq-toast');
    while (toasts.length > maxToasts) {
      var oldest = toasts[toasts.length - 1];
      dismiss(oldest);
      toasts = wrap.querySelectorAll('.rq-toast');
    }

    var duration = getDuration(type);
    if (duration > 0) {
      var timer = setTimeout(function() {
        dismiss(toast);
      }, duration);
      timers.set(toast, timer);

      toast.addEventListener('mouseenter', function() {
        clearTimeout(timer);
      });

      toast.addEventListener('mouseleave', function() {
        if (toast.classList.contains('out') || !toast.parentNode) return;
        timer = setTimeout(function() {
          dismiss(toast);
        }, duration);
        timers.set(toast, timer);
      });
    }
  }

  window.showToast = showToast;
})();
