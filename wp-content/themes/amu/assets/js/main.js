// Mobile nav toggle. Kept tiny on purpose — no framework for one button.
(function () {
  var btn = document.querySelector('.nav-toggle');
  var nav = document.querySelector('.main-nav');
  if (!btn || !nav) return;
  btn.addEventListener('click', function () {
    var open = nav.classList.toggle('open');
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
  });
})();
