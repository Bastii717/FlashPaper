/* FlashPaper UI: theme switch, copy buttons, byte counter, clear screen. No dependencies. */
(function () {
	'use strict';

	var root = document.documentElement;
	var each = function (sel, fn, scope) { Array.prototype.forEach.call((scope || document).querySelectorAll(sel), fn); };

	// ---- theme: stored choice wins, otherwise the system preference ----
	// (the stored value is applied before first paint by the inline script in header.php)
	var light = window.matchMedia ? window.matchMedia('(prefers-color-scheme: light)') : null;

	function currentTheme() {
		return root.getAttribute('data-theme') || (light && light.matches ? 'light' : 'dark');
	}

	function paintToggles() {
		var t = currentTheme();
		each('[data-theme-toggle]', function (b) {
			b.textContent = (t === 'light') ? '☀ light' : '☾ dark';
			b.setAttribute('aria-pressed', t === 'light' ? 'true' : 'false');
			b.hidden = false;
		});
	}

	function toggleTheme() {
		var t = (currentTheme() === 'light') ? 'dark' : 'light';
		root.setAttribute('data-theme', t);
		try { localStorage.setItem('fp-theme', t); } catch (e) { }
		paintToggles();
	}

	each('[data-theme-toggle]', function (b) { b.addEventListener('click', toggleTheme); });

	// Shift+D toggles too, but never while typing (a capital D in the secret is just a D)
	document.addEventListener('keydown', function (e) {
		if ( !e.shiftKey || e.key !== 'D' || e.ctrlKey || e.metaKey || e.altKey ) { return; }
		var el = e.target, tag = (el.tagName || '').toLowerCase();
		if ( tag === 'textarea' || tag === 'input' || tag === 'select' || el.isContentEditable ) { return; }
		toggleTheme();
	});

	if ( light && light.addEventListener ) { light.addEventListener('change', paintToggles); }
	paintToggles();

	// ---- copy buttons: <button data-copy="#id" data-done="✓ Copied"> ----
	function selectContents(el) {
		if ( typeof el.select === 'function' ) { el.select(); return; }
		var range = document.createRange();
		range.selectNodeContents(el);
		var sel = window.getSelection();
		sel.removeAllRanges();
		sel.addRange(range);
	}

	function copy(el) {
		var text = (el.value !== undefined) ? el.value : el.textContent;
		if ( navigator.clipboard && window.isSecureContext ) {
			return navigator.clipboard.writeText(text);
		}
		selectContents(el);
		return document.execCommand('copy') ? Promise.resolve() : Promise.reject();
	}

	function flash(button) {
		if ( !button.hasAttribute('data-label') ) { button.setAttribute('data-label', button.textContent); }
		button.textContent = button.getAttribute('data-done') || 'Copied';
		button.classList.add('done');
		clearTimeout(button._fpTimer);
		button._fpTimer = setTimeout(function () {
			button.textContent = button.getAttribute('data-label');
			button.classList.remove('done');
		}, 1800);
	}

	document.addEventListener('click', function (e) {
		var b = e.target.closest('[data-copy]');
		if ( b ) {
			var src = document.querySelector(b.getAttribute('data-copy'));
			if ( src ) {
				copy(src).then(function () { flash(b); }, function () { selectContents(src); });
			}
			return;
		}

		// "clear screen": removes the secret from the page (it's already gone from the server)
		var c = e.target.closest('[data-clear]');
		if ( c ) {
			var scope = c.closest('section') || document;
			each('pre.secret', function (el) { el.textContent = ''; }, scope);
			each('[data-clearable]', function (el) { el.hidden = true; }, scope);
			each('[data-cleared]', function (el) { el.hidden = false; }, scope);
		}
	});

	// ---- secret counter: counts UTF-8 bytes like the server's strlen() ----
	var ta = document.querySelector('textarea[data-count]');
	if ( ta ) {
		var max = parseInt(ta.getAttribute('data-max'), 10);
		var out = document.querySelector(ta.getAttribute('data-count'));
		var slot = document.querySelector('[data-toolong]');
		var enc = new TextEncoder();

		var update = function () {
			var n = enc.encode(ta.value.replace(/\r\n/g, '\n')).length;
			var over = n > max;
			if ( out ) {
				out.textContent = n + '/' + max;
				out.classList.toggle('over', over);
			}
			return over;
		};

		ta.addEventListener('input', function () {
			if ( !update() && slot ) { slot.hidden = true; }
		});
		if ( ta.form ) {
			ta.form.addEventListener('submit', function (e) {
				if ( update() ) {
					e.preventDefault();
					if ( slot ) { slot.hidden = false; }
					ta.focus();
				}
			});
		}
		update();
	}

	// ---- template picker navigates to ?t=<name> ----
	each('select[data-template-nav]', function (s) {
		s.addEventListener('change', function () { if ( s.value ) { window.location.href = s.value; } });
	});

	// ---- one-shot forms (reveal): no double submit ----
	each('form[data-once]', function (f) {
		f.addEventListener('submit', function () {
			each('button[type="submit"]', function (b) { b.disabled = true; }, f);
		});
	});

	// don't let the back/forward cache bring a typed secret back
	window.addEventListener('pagehide', function () {
		var s = document.getElementById('secret');
		if ( s ) { s.value = ''; }
	});
})();
