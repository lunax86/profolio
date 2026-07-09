<?php declare(strict_types=1); ?>
<?php /** @var bool $loggedIn */ ?>
<?php if ($loggedIn): ?>
        </div><!-- .content -->
    </div><!-- .main -->
</div><!-- .app -->
<?php else: ?>
</div><!-- .login-shell -->
<?php endif; ?>
<script>
    (function () {
        // Přepínač světlý/tmavý režim (ukládá volbu do localStorage).
        var themeButton = document.getElementById('themeBtn');
        if (themeButton) {
            themeButton.addEventListener('click', function () {
                var root = document.documentElement;
                var currentMode = root.getAttribute('data-theme');
                if (!currentMode) {
                    currentMode = matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                }
                var nextMode = currentMode === 'dark' ? 'light' : 'dark';
                root.setAttribute('data-theme', nextMode);
                try { localStorage.setItem('admin-theme', nextMode); } catch (error) { /* neukládej */ }
            });
        }

        // Otevírání a zavírání menu na mobilu.
        var app = document.getElementById('app');
        var closeNav = function () { app && app.classList.remove('nav-open'); };
        var burger = document.getElementById('burger');
        if (burger) {
            burger.addEventListener('click', function () { app.classList.toggle('nav-open'); });
        }
        var backdrop = document.getElementById('navBackdrop');
        if (backdrop) { backdrop.addEventListener('click', closeNav); }
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') { closeNav(); }
        });

        // Živá počítadla znaků: prvek .counter[data-counter="#id"] u pole s maxlength.
        document.querySelectorAll('.counter[data-counter]').forEach(function (counter) {
            var input = document.querySelector(counter.getAttribute('data-counter'));
            if (!input) { return; }
            var max = input.getAttribute('maxlength');
            var update = function () {
                counter.textContent = input.value.length + ' / ' + max + ' znaků';
            };
            update();
            input.addEventListener('input', update);
        });

        // Zobrazení/skrytí hesla u polí s tlačítkem .pw-toggle.
        document.querySelectorAll('.pw-toggle').forEach(function (toggle) {
            toggle.addEventListener('click', function () {
                var input = toggle.parentElement.querySelector('input');
                var showing = input.type === 'password';
                input.type = showing ? 'text' : 'password';
                toggle.querySelector('use').setAttribute('href', showing ? '#i-eye-off' : '#i-eye');
                toggle.setAttribute('aria-label', showing ? 'Skrýt heslo' : 'Zobrazit heslo');
            });
        });
    })();
</script>
</body>
</html>
