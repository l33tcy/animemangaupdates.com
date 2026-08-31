/* AnimeMangaUpdates, navbar drawer, search overlay w/ live suggestions, theme toggle.
 * All dynamic content is built with DOM APIs + textContent (no innerHTML) to keep
 * untrusted input (query strings, REST titles) inert. */
(function () {
  'use strict';
  var data = window.amuData || {};
  var doc = document, root = doc.documentElement, body = doc.body;
  var $ = function (s, c) { return (c || doc).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || doc).querySelectorAll(s)); };

  function el(tag, cls, text) { var n = doc.createElement(tag); if (cls) n.className = cls; if (text != null) n.textContent = text; return n; }
  function clear(node) { while (node && node.firstChild) node.removeChild(node.firstChild); }
  function safeUrl(u) { // only allow http(s) same-origin-ish urls in hrefs
    if (typeof u !== 'string') return '#';
    return /^https?:\/\//i.test(u) || u.charAt(0) === '/' ? u : '#';
  }

  /* ---------------------------------------------------- theme toggle (navbar + drawer) */
  $$('.js-theme-toggle').forEach(function (toggle) {
    toggle.addEventListener('click', function () {
      var next = root.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
      root.setAttribute('data-theme', next);
      try { localStorage.setItem('amu-theme', next); } catch (e) {}
    });
  });

  /* ---------------------------------------------------- open/close helpers */
  function open(node) { if (!node) return; node.classList.add('open'); node.setAttribute('aria-hidden', 'false'); body.classList.add('no-scroll'); }
  function close(node) { if (!node) return; node.classList.remove('open'); node.setAttribute('aria-hidden', 'true'); if (!$('.mobile-drawer.open') && !$('.search-overlay.open')) body.classList.remove('no-scroll'); }

  /* ---------------------------------------------------- mobile drawer */
  var drawer = $('#amuDrawer');
  $$('.js-drawer-open').forEach(function (b) { b.addEventListener('click', function () { open(drawer); }); });
  $$('.js-drawer-close').forEach(function (b) { b.addEventListener('click', function () { close(drawer); }); });
  if (drawer) $$('a', drawer).forEach(function (a) { a.addEventListener('click', function () { close(drawer); }); });

  /* ---------------------------------------------------- search overlay */
  var overlay = $('#amuSearch');
  var input = $('#amuSearchInput');
  var suggest = $('#amuSuggest');
  var historyEl = $('#amuHistory');
  var HKEY = 'amu-history';

  function readHistory() { try { return JSON.parse(localStorage.getItem(HKEY) || '[]'); } catch (e) { return []; } }
  function saveQuery(q) {
    q = (q || '').trim(); if (!q) return;
    var h = readHistory().filter(function (x) { return x.toLowerCase() !== q.toLowerCase(); });
    h.unshift(q); h = h.slice(0, 6);
    try { localStorage.setItem(HKEY, JSON.stringify(h)); } catch (e) {}
  }
  function historyRow(q) {
    var li = el('li'), a = el('a');
    a.setAttribute('href', safeUrl(data.homeUrl + '?s=' + encodeURIComponent(q)));
    var sq = el('span', 'sq'); sq.style.setProperty('--sq', '#8b8492');
    a.appendChild(sq); a.appendChild(doc.createTextNode(q));
    li.appendChild(a); return li;
  }
  function renderHistory() {
    if (!historyEl) return;
    clear(historyEl);
    var h = readHistory();
    if (!h.length) { historyEl.appendChild(el('li', 'muted', 'No search history')); return; }
    h.forEach(function (q) { historyEl.appendChild(historyRow(q)); });
  }

  function openSearch() { open(overlay); renderHistory(); if (input) setTimeout(function () { input.focus(); }, 60); }
  $$('.js-search-open').forEach(function (b) { b.addEventListener('click', openSearch); });
  $$('.js-search-close').forEach(function (b) { b.addEventListener('click', function () { close(overlay); }); });
  if (overlay) overlay.addEventListener('mousedown', function (e) { if (e.target === overlay) close(overlay); });

  var form = overlay && $('.search-box', overlay);
  if (form) form.addEventListener('submit', function () { saveQuery(input.value); });

  /* ---------------------------------------------------- live suggestions (WP REST) */
  function suggestRow(item) {
    var a = el('a');
    a.setAttribute('href', safeUrl(item.url));
    a.appendChild(el('span', 's-title', typeof item.title === 'string' ? item.title : ''));
    a.appendChild(el('span', 's-kind', item.subtype || 'post'));
    return a;
  }
  var timer, controller;
  function fetchSuggest(q) {
    if (!suggest || !data.searchRest) return;
    if (controller) controller.abort();
    controller = ('AbortController' in window) ? new AbortController() : null;
    var url = data.searchRest + '?search=' + encodeURIComponent(q) + '&per_page=6&_fields=id,title,url,subtype';
    fetch(url, { signal: controller ? controller.signal : undefined })
      .then(function (r) { return r.ok ? r.json() : []; })
      .then(function (items) {
        clear(suggest);
        if (!items || !items.length) { suggest.appendChild(el('div', 's-empty', 'No results for “' + q + '”, press Enter to search anyway.')); return; }
        items.forEach(function (it) { suggest.appendChild(suggestRow(it)); });
      })
      .catch(function () {});
  }
  if (input) {
    input.addEventListener('input', function () {
      var q = input.value.trim();
      clearTimeout(timer);
      if (q.length < 2) { clear(suggest); return; }
      timer = setTimeout(function () { fetchSuggest(q); }, 200);
    });
  }

  /* ---------------------------------------------------- escape closes overlays */
  doc.addEventListener('keydown', function (e) { if (e.key === 'Escape') { close(overlay); close(drawer); } });

  /* ---------------------------------------------------- back to top */
  var toTop = $('.js-back-to-top');
  if (toTop) {
    var onScroll = function () { toTop.classList.toggle('show', (window.pageYOffset || doc.documentElement.scrollTop) > 500); };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
    toTop.addEventListener('click', function () {
      var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      window.scrollTo({ top: 0, behavior: reduce ? 'auto' : 'smooth' });
    });
  }
})();

/* email reveal (anti-spam): assemble address on click */
(function () {
  Array.prototype.forEach.call(document.querySelectorAll('.js-reveal-email'), function (btn) {
    btn.addEventListener('click', function () {
      var addr = (btn.getAttribute('data-u') || '') + '@' + (btn.getAttribute('data-d') || '');
      var a = document.createElement('a');
      a.href = 'mailto:' + addr; a.textContent = addr; a.className = 'reveal-email revealed';
      btn.parentNode.replaceChild(a, btn);
    });
  });
})();

/* hero slider: auto-advancing trending carousel (pauses on hover / hidden tab; respects reduced-motion) */
(function () {
  var root = document.querySelector('.hero-slider');
  if (!root) return;
  var slides = Array.prototype.slice.call(root.querySelectorAll('.hs-slide'));
  var dots = Array.prototype.slice.call(root.querySelectorAll('.hs-dot'));
  if (slides.length < 2) return;
  var bar = root.querySelector('.hs-progress span');
  var interval = parseInt(root.getAttribute('data-interval'), 10) || 5500;
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var cur = 0, timer = null;

  function show(n) {
    n = (n + slides.length) % slides.length;
    if (n === cur) return;
    slides[cur].classList.remove('is-active');
    slides[cur].setAttribute('aria-hidden', 'true');
    if (dots[cur]) dots[cur].classList.remove('is-active');
    cur = n;
    slides[cur].classList.add('is-active');
    slides[cur].removeAttribute('aria-hidden');
    if (dots[cur]) dots[cur].classList.add('is-active');
  }

  function runBar() {
    if (!bar || reduce) return;
    bar.classList.remove('run');
    bar.style.width = '0';
    void bar.offsetWidth; /* reflow so the transition restarts cleanly */
    bar.style.transitionDuration = interval + 'ms';
    bar.classList.add('run');
    bar.style.width = '100%';
  }
  function start() {
    if (reduce || timer) return;
    runBar();
    timer = setInterval(function () { show(cur + 1); runBar(); }, interval);
  }
  function stop() {
    if (timer) { clearInterval(timer); timer = null; }
    if (bar) { bar.classList.remove('run'); bar.style.width = '0'; }
  }
  function restart() { stop(); start(); }

  var next = root.querySelector('.hs-next'), prev = root.querySelector('.hs-prev');
  if (next) next.addEventListener('click', function () { show(cur + 1); restart(); });
  if (prev) prev.addEventListener('click', function () { show(cur - 1); restart(); });
  dots.forEach(function (d, i) { d.addEventListener('click', function () { show(i); restart(); }); });
  root.addEventListener('mouseenter', stop);
  root.addEventListener('mouseleave', start);
  document.addEventListener('visibilitychange', function () { document.hidden ? stop() : start(); });

  start();
})();
