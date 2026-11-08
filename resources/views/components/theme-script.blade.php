@props(['key' => 'libby-theme'])
{{-- Hell/Dunkel vor dem ersten Zeichnen setzen (kein weißes Aufblitzen). Gespeicherte Wahl > Systemeinstellung.
     Der Umschalter ist <x-theme-toggle /> – der Klick wird hier per Event-Delegation behandelt. --}}
<script>
(function () {
    var root = document.documentElement, key = @js($key), stored = null;
    try { stored = localStorage.getItem(key); } catch (e) {}
    var system = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;
    var current = function () { return root.dataset.theme === 'dark' ? 'dark' : 'light'; };

    var apply = function (theme) {
        root.dataset.theme = theme;
        var label = theme === 'dark' ? 'Helles Design' : 'Dunkles Design';
        document.querySelectorAll('[data-theme-toggle]').forEach(function (button) {
            button.setAttribute('aria-label', label);
            button.setAttribute('title', label);
            var text = button.querySelector('[data-theme-label]');
            if (text) text.textContent = label;
        });
        var meta = document.querySelector('meta[name="theme-color"]');
        if (meta) meta.setAttribute('content', theme === 'dark' ? '#0e0e10' : '#ffffff');
    };

    apply(stored === 'light' || stored === 'dark' ? stored : (system && system.matches ? 'dark' : 'light'));
    document.addEventListener('DOMContentLoaded', function () { apply(current()); });

    // Ohne eigene Wahl folgt die Seite der Systemeinstellung, auch wenn sie sich ändert
    if (system && system.addEventListener) {
        system.addEventListener('change', function (e) {
            var saved = null;
            try { saved = localStorage.getItem(key); } catch (err) {}
            if (saved !== 'light' && saved !== 'dark') apply(e.matches ? 'dark' : 'light');
        });
    }

    document.addEventListener('click', function (e) {
        if (!e.target.closest || !e.target.closest('[data-theme-toggle]')) return;
        var next = current() === 'dark' ? 'light' : 'dark';
        apply(next);
        try { localStorage.setItem(key, next); } catch (err) {}
    });
})();
</script>
