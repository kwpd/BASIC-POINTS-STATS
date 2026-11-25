(function () {
  'use strict';

  function onEvent(el, evt, handler) {
    if (!el) return;
    if (el.addEventListener) {
      el.addEventListener(evt, handler, false);
    } else if (el.attachEvent) {
      el.attachEvent('on' + evt, handler);
    }
  }

  function findClosestAttr(node, attr) {
    while (node && node.nodeType === 1) {
      if (node.getAttribute(attr) !== null) {
        return node;
      }
      node = node.parentNode;
    }
    return null;
  }

  function showToast() {
    var t = document.getElementById('bpsToast');
    if (!t) return;
    if (t.classList) {
      t.classList.add('show');
    } else {
      t.className = 'bps-toast show';
    }
    clearTimeout(window._bpsToastTimer);
    window._bpsToastTimer = setTimeout(function () {
      if (t.classList) {
        t.classList.remove('show');
      } else {
        t.className = 'bps-toast';
      }
    }, 2000);
  }

  function copyText(text) {
    if (!text) return;
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(text).then(showToast).catch(function () {});
      return;
    }
    var el = document.createElement('textarea');
    el.value = text;
    el.setAttribute('readonly', '');
    el.style.position = 'absolute';
    el.style.left = '-9999px';
    document.body.appendChild(el);
    el.select();
    try {
      document.execCommand('copy');
      showToast();
    } catch (e) {}
    document.body.removeChild(el);
  }

  onEvent(document, 'click', function (ev) {
    var e = ev || window.event;
    var target = e ? (e.target || e.srcElement) : null;
    var btn = findClosestAttr(target, 'data-copy');
    if (!btn) return;
    var val = btn.getAttribute('data-copy');
    if (val) {
      copyText(val);
    }
  });

  var weaponFilter = document.getElementById('weaponQuickFilter');
  if (weaponFilter) {
    var filterWeapons = function () {
      var q = (weaponFilter.value || '').toLowerCase().replace(/^\s+|\s+$/g, '');
      var blocks = document.querySelectorAll
        ? document.querySelectorAll('#weaponCatalogRoot .weapon-category-block')
        : [];
      for (var i = 0; i < blocks.length; i++) {
        var cards = blocks[i].querySelectorAll('.weapon-card');
        var visibleCount = 0;
        for (var j = 0; j < cards.length; j++) {
          var name = cards[j].getAttribute('data-wname') || '';
          var show = (q === '' || name.indexOf(q) !== -1);
          cards[j].style.display = show ? 'inline-flex' : 'none';
          if (show) visibleCount++;
        }
        blocks[i].style.display = visibleCount > 0 ? 'block' : 'none';
      }
    };
    onEvent(weaponFilter, 'input', filterWeapons);
    onEvent(weaponFilter, 'keyup', filterWeapons);
  }

  var autoSubmitIds = ['bpsServerSelect', 'bpsLangSelect'];
  for (var k = 0; k < autoSubmitIds.length; k++) {
    var sel = document.getElementById(autoSubmitIds[k]);
    if (sel && sel.form) {
      (function (formEl, selectEl) {
        onEvent(selectEl, 'change', function () {
          formEl.submit();
        });
      })(sel.form, sel);
    }
  }
})();
