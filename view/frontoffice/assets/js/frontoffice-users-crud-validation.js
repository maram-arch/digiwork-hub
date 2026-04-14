(function () {
    const form = document.getElementById('frontofficeUserForm');
    const isUpdate = form.querySelector('input[name="action"]').value === 'update';

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

        if ((!isUpdate && password.length < 6) || (isUpdate && password !== '' && password.length < 6)) {
            showError('foPasswordError', 'Mot de passe: minimum 6 caracteres.');
            ok = false;
        } else {
            showError('foPasswordError', '');
        }

        if (!/^[0-9]{8,15}$/.test(tel)) {
            showError('foTelError', 'Telephone invalide (8 a 15 chiffres).');
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
})();
