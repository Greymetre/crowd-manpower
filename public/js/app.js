// Crowd Manpower — tiny vanilla helpers (no dependencies)
document.addEventListener('DOMContentLoaded', () => {
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Show / hide password
    document.querySelectorAll('[data-toggle-password]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const input = btn.parentElement.querySelector('input');
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.classList.toggle('is-on', show);
            btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            input.focus();
        });
    });

    // Fill demo credentials
    document.querySelectorAll('[data-demo-fill]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const email = document.getElementById('email');
            const password = document.getElementById('password');
            email.value = btn.dataset.email;
            password.value = btn.dataset.password;
            document.querySelector('[data-login-form] .btn')?.focus();
        });
    });

    // Loading state on submit (native validation still runs first)
    document.querySelectorAll('[data-login-form]').forEach((form) => {
        form.addEventListener('submit', (e) => {
            if (!form.checkValidity()) {
                e.preventDefault();
                form.reportValidity();
                return;
            }
            form.querySelector('.btn')?.classList.add('is-loading');
        });
    });

    // Count-up numbers
    document.querySelectorAll('[data-count]').forEach((el) => {
        const target = Number(el.dataset.count);
        if (reduceMotion) return; // server-rendered value stays as is
        el.textContent = '0';
        const duration = 1600;
        const delay = 700;
        let start;
        const tick = (now) => {
            start ??= now;
            const t = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - t, 3);
            el.textContent = Math.round(target * eased).toLocaleString();
            if (t < 1) requestAnimationFrame(tick);
        };
        setTimeout(() => requestAnimationFrame(tick), delay);
        // Safety net: always land on the real value (e.g. rAF paused in a background tab)
        setTimeout(() => (el.textContent = target.toLocaleString()), delay + duration + 200);
    });

    // Subtle mouse parallax on showcase cards (desktop only)
    const stage = document.querySelector('[data-parallax]');
    if (stage && !reduceMotion && window.matchMedia('(pointer: fine)').matches) {
        const cards = stage.querySelectorAll('[data-depth]');
        let frame;
        stage.addEventListener('mousemove', (e) => {
            cancelAnimationFrame(frame);
            frame = requestAnimationFrame(() => {
                const r = stage.getBoundingClientRect();
                const x = (e.clientX - r.left) / r.width - 0.5;
                const y = (e.clientY - r.top) / r.height - 0.5;
                cards.forEach((c) => {
                    const d = Number(c.dataset.depth);
                    c.style.translate = `${-x * d}px ${-y * d}px`;
                });
            });
        });
        stage.addEventListener('mouseleave', () => cards.forEach((c) => (c.style.translate = '')));
    }

    // ----- Entry form -----
    const entry = document.querySelector('[data-entry-form]');
    if (entry) {
        // DOB -> Age
        const dob = entry.querySelector('[data-dob]');
        const age = entry.querySelector('[data-age]');
        dob?.addEventListener('change', () => {
            if (!dob.value) return;
            const b = new Date(dob.value);
            const t = new Date();
            let years = t.getFullYear() - b.getFullYear();
            if (t.getMonth() < b.getMonth() || (t.getMonth() === b.getMonth() && t.getDate() < b.getDate())) years--;
            if (years >= 0) {
                age.value = years;
                age.classList.remove('is-auto'); void age.offsetWidth; age.classList.add('is-auto');
            }
        });

        // Warn before leaving with unsaved changes
        let dirty = false;
        entry.addEventListener('input', () => (dirty = true));
        entry.addEventListener('submit', () => (dirty = false));
        entry.querySelectorAll('[data-nav]').forEach((a) =>
            a.addEventListener('click', (e) => {
                if (dirty && !confirm('You have unsaved changes. Leave this record without saving?')) e.preventDefault();
            })
        );

        // Ctrl/Cmd + S = Save Record
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
                e.preventDefault();
                entry.querySelector('button[value="save"]')?.click();
            }
        });
    }

    // ----- Clickable table rows -----
    document.querySelectorAll('tr[data-href]').forEach((tr) =>
        tr.addEventListener('click', (e) => {
            if (!e.target.closest('a, button, form')) window.location = tr.dataset.href;
        })
    );

    // ----- Import drop zone -----
    const drop = document.querySelector('[data-drop]');
    if (drop) {
        const input = drop.querySelector('input[type=file]');
        const label = drop.querySelector('[data-drop-label]');
        const show = () => {
            const f = input.files[0];
            drop.classList.toggle('has-file', !!f);
            if (f) label.textContent = `${f.name} (${(f.size / 1024).toFixed(0)} KB)`;
        };
        input.addEventListener('change', show);
        ['dragenter', 'dragover'].forEach((ev) => drop.addEventListener(ev, () => drop.classList.add('is-over')));
        ['dragleave', 'drop'].forEach((ev) => drop.addEventListener(ev, () => drop.classList.remove('is-over')));
        drop.closest('form').addEventListener('submit', () => drop.closest('form').querySelector('.btn')?.classList.add('is-loading'));
    }
});
