<?php
/**
 * WordPress fatal-error drop-in — the custom HTTP 500 ("critical error") page.
 *
 * WordPress includes this file automatically when its shutdown handler catches a
 * fatal PHP error, in place of the default "There has been a critical error"
 * screen. It MUST be fully self-contained: no theme, no design-system stylesheet,
 * and no WordPress functions (the app has already crashed by the time we get here).
 */

if ( ! headers_sent() ) {
	header( 'HTTP/1.1 500 Internal Server Error' );
	header( 'Content-Type: text/html; charset=utf-8' );
	header( 'Retry-After: 120' );
}
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Temporarily unavailable | AnimeMangaUpdates</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@600;800&display=swap" rel="stylesheet">
<style>
  :root { --bg:#0a0b0e; --ink:#f4f6fa; --muted:#9aa3b2; --red:#ff3040; --blue:#2f6bff; --line:#272b35; }
  * { box-sizing:border-box; }
  html, body { margin:0; height:100%; }
  body { background:var(--bg); color:var(--ink); font-family:"Bricolage Grotesque", ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; display:grid; place-items:center; min-height:100vh; padding:40px 20px; position:relative; overflow:hidden; }
  body::before { content:""; position:absolute; inset:0; background:radial-gradient(60% 55% at 50% 28%, rgba(255,48,64,.16), transparent 70%); pointer-events:none; }
  body::after { content:""; position:absolute; inset:0; background-image:radial-gradient(rgba(255,255,255,.04) 1px, transparent 1.5px); background-size:22px 22px; pointer-events:none; }
  .wrap { position:relative; text-align:center; max-width:620px; }
  .brand { font-size:12px; font-weight:600; letter-spacing:.22em; text-transform:uppercase; color:var(--muted); margin:0 0 26px; }
  .code { font-weight:800; font-size:clamp(110px, 26vw, 220px); line-height:.9; letter-spacing:-.04em; margin:0; background:linear-gradient(120deg, var(--red), var(--blue)); -webkit-background-clip:text; background-clip:text; color:transparent; }
  h1 { font-weight:800; font-size:clamp(24px, 4.5vw, 36px); letter-spacing:-.02em; margin:12px 0 10px; }
  .msg { color:var(--muted); font-size:17px; line-height:1.6; margin:0 auto 30px; max-width:470px; }
  .actions { display:flex; gap:12px; justify-content:center; flex-wrap:wrap; }
  .btn { display:inline-flex; align-items:center; height:48px; padding:0 22px; border-radius:11px; font-weight:800; font-size:15px; text-decoration:none; border:1.5px solid var(--line); color:var(--ink); transition:border-color .15s, background .15s, transform .15s; cursor:pointer; background:none; }
  .btn:hover { transform:translateY(-2px); border-color:var(--ink); }
  .btn-primary { background:var(--red); border-color:var(--red); color:#fff; }
  .btn-primary:hover { background:#e0202f; border-color:#e0202f; }
</style>
</head>
<body>
  <main class="wrap">
    <p class="brand">AnimeMangaUpdates</p>
    <p class="code">500</p>
    <h1>Something went wrong on our end.</h1>
    <p class="msg">We hit an unexpected error while loading this page. It is not you, it is us. Please try again in a few moments.</p>
    <div class="actions">
      <button class="btn btn-primary" type="button" onclick="location.reload()">Try again</button>
      <a class="btn" href="/">Go to homepage</a>
    </div>
  </main>
</body>
</html>
