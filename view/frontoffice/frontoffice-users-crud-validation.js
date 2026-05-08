(function () {
    const form = document.getElementById('frontofficeUserForm');
    const isUpdate = form.querySelector('input[name="action"]').value === 'update';
    const FO_PASSWORD_SPECIALS = '!@#$%^&*+-=:,.<>?[]{}()|/~';

    function foPasswordHasSpecial(value) {
        for (let i = 0; i < value.length; i++) {
            if (FO_PASSWORD_SPECIALS.indexOf(value[i]) !== -1) {
                return true;
            }
        }
        return false;
    }

    /** @returns {{checks: Record<string,boolean>, met: number, meetsPolicy: boolean}} */
    function analyzeFoPassword(value) {
        const v = typeof value === 'string' ? value : '';
        const checks = {
            longueur: v.length >= 10,
            majuscule: /[A-Z]/.test(v),
            minuscule: /[a-z]/.test(v),
            chiffre: /[0-9]/.test(v),
            special: foPasswordHasSpecial(v),
        };
        const met = Object.keys(checks).filter(function (k) { return checks[k]; }).length;
        const meetsPolicy = v.length > 0 && Object.keys(checks).every(function (k) { return checks[k]; });
        return { checks: checks, met: met, meetsPolicy: meetsPolicy };
    }

    function renderFoPasswordStrength() {
        const input = document.getElementById('foPassword');
        if (!input) { return; }

        let box = document.getElementById('foPasswordStrength');
        if (!box) {
            box = document.createElement('div');
            box.id = 'foPasswordStrength';
            input.parentNode.insertBefore(box, input);
        }

        const value = input.value;

        if (value === '') {
            box.style.display = 'none';
            box.innerHTML = '';
            return;
        }

        const a = analyzeFoPassword(value);
        box.style.display = 'block';

        let label, color, bg, border;
        if (a.met <= 2) {
            label = 'Faible';
            color = '#c0392b';
            bg = '#fdecea';
            border = '1px solid #f5c6cb';
        } else if (a.met <= 4) {
            label = 'Moyen';
            color = '#e67e22';
            bg = '#fff3e0';
            border = '1px solid #ffe0b2';
        } else {
            label = 'Fort';
            color = '#27ae60';
            bg = '#eafaf1';
            border = '1px solid #c3e6cb';
        }

        box.style.cssText = 'padding:6px 10px;border-radius:6px;font-size:13px;margin-bottom:6px;' +
            'color:' + color + ';background:' + bg + ';border:' + border + ';';

        const criteriaLabels = {
            longueur: 'au moins 10 caractères',
            majuscule: 'une majuscule',
            minuscule: 'une minuscule',
            chiffre: 'un chiffre',
            special: 'un caractère spécial (!@#$%^&*+-=:,.<>?[]{}()|/~)',
        };
        const missing = Object.keys(a.checks).filter(function (k) { return !a.checks[k]; });
        const missingText = missing.length > 0
            ? '<div style="margin-top:3px;font-size:12px;">Manque : ' + missing.map(function (k) { return criteriaLabels[k]; }).join(', ') + '</div>'
            : '';

        box.innerHTML = '<strong>' + label + '</strong>' + missingText;
    }

    /** @returns {string} message d'erreur ou chaîne vide */
    function foPasswordMessage(password) {
        if (password === '') {
            return isUpdate ? '' : 'Mot de passe obligatoire.';
        }
        if (/\s/.test(password)) {
            return 'Le mot de passe ne doit pas contenir d\'espaces.';
        }
        const a = analyzeFoPassword(password);
        if (!a.checks.longueur) {
            return 'Le mot de passe doit contenir au moins 10 caractères.';
        }
        if (!a.checks.majuscule) {
            return 'Le mot de passe doit contenir au moins une majuscule.';
        }
        if (!a.checks.minuscule) {
            return 'Le mot de passe doit contenir au moins une minuscule.';
        }
        if (!a.checks.chiffre) {
            return 'Le mot de passe doit contenir au moins un chiffre.';
        }
        if (!a.checks.special) {
            return 'Le mot de passe doit contenir au moins un caractère spécial (exemple: @ # $ % & *).';
        }
        return '';
    }

    function showError(id, message) {
        const el = document.getElementById(id);
        el.textContent = message;
        el.style.display = message ? 'block' : 'none';
    }

    function validate() {
        const email = document.getElementById('foEmail').value.trim();
        const password = document.getElementById('foPassword').value.trim();
        const tel = document.getElementById('foTel').value.trim();

        let ok = true;

        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            showError('foEmailError', 'Email invalide.');
            ok = false;
        } else {
            showError('foEmailError', '');
        }

        const pwErr = foPasswordMessage(password);
        if (pwErr) {
            showError('foPasswordError', pwErr);
            ok = false;
        } else {
            showError('foPasswordError', '');
        }

        if (!/^[0-9]{8}$/.test(tel)) {
            showError('foTelError', 'Telephone invalide (exactement 8 chiffres).');
            ok = false;
        } else {
            showError('foTelError', '');
        }

        return ok;
    }

    form.addEventListener('submit', function (event) {
        if (!validate()) {
            event.preventDefault();
        }
    });

    var foPasswordInput = document.getElementById('foPassword');
    if (foPasswordInput) {
        foPasswordInput.addEventListener('input', renderFoPasswordStrength);
    }
})();
