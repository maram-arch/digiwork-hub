(function () {
    const form = document.getElementById('backofficeUserForm');
    const isUpdate = form.querySelector('input[name="action"]').value === 'update';
    const allowedRoles = ['condidat', 'admin', 'entreprise', 'sponsor'];

    function showError(id, message) {
        const el = document.getElementById(id);
        el.textContent = message;
        el.style.display = message ? 'block' : 'none';
    }

    function validate() {
        const email = document.getElementById('boEmail').value.trim();
        const password = document.getElementById('boPassword').value.trim();
        const role = document.getElementById('boRole').value.trim();
        const tel = document.getElementById('boTel').value.trim();

        let ok = true;

        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            showError('boEmailError', 'Email invalide.');
            ok = false;
        } else {
            showError('boEmailError', '');
        }

        if ((!isUpdate && password.length < 6) || (isUpdate && password !== '' && password.length < 6)) {
            showError('boPasswordError', 'Mot de passe: minimum 6 caracteres.');
            ok = false;
        } else {
            showError('boPasswordError', '');
        }

        if (!allowedRoles.includes(role)) {
            showError('boRoleError', 'Role invalide.');
            ok = false;
        } else {
            showError('boRoleError', '');
        }

        if (!/^[0-9]{8}$/.test(tel)) {
            showError('boTelError', 'Telephone invalide (exactement 8 chiffres).');
            ok = false;
        } else {
            showError('boTelError', '');
        }

        return ok;
    }

    form.addEventListener('submit', function (event) {
        if (!validate()) {
            event.preventDefault();
        }
    });
})();
