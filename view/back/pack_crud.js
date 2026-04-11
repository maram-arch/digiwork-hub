document.addEventListener('DOMContentLoaded', function() {

    function validateForm() {
        let isValid = true;
        // Hide all error messages initially
        document.querySelectorAll('.error-msg').forEach(el => el.style.display = 'none');

        const nom = document.getElementById('nom').value.trim();
        if (!nom) {
            document.getElementById('nomError').style.display = 'inline';
            isValid = false;
        }

        const prix = parseFloat(document.getElementById('prix').value);
        if (isNaN(prix) || prix < 0) {
            document.getElementById('prixError').style.display = 'inline';
            isValid = false;
        }

        const duree = document.getElementById('duree').value;
        if (!duree) {
            document.getElementById('dureeError').style.display = 'inline';
            isValid = false;
        }

        const desc = document.getElementById('description').value.trim();
        if (!desc) {
            document.getElementById('descError').style.display = 'inline';
            isValid = false;
        }

        const nb = parseInt(document.getElementById('nb').value);
        if (isNaN(nb) || nb <= 0) {
            document.getElementById('nbError').style.display = 'inline';
            isValid = false;
        }

        return isValid;
    }

    const addForm = document.getElementById('addPackForm');
    if (addForm) {
        addForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (validateForm()) {
                const formData = new FormData(addForm);
                formData.append('action', 'add');
                formData.append('ajax', '1');

                fetch('../../controller/PackController.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert(data.message);
                        window.location.reload(); // Reload to see the appended row
                    }
                })
                .catch(err => console.error("Error adding:", err));
            }
        });
    }

    const updateForm = document.getElementById('updatePackForm');
    if (updateForm) {
        updateForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (validateForm()) {
                const formData = new FormData(updateForm);
                formData.append('action', 'update');
                formData.append('ajax', '1');

                fetch('../../controller/PackController.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert(data.message);
                        window.location.href = 'dashboard_packs.php'; // Return to list
                    }
                })
                .catch(err => console.error("Error updating:", err));
            }
        });
    }
});

// Global function so it can be called from onclick handlers in the HTML
window.deletePack = function(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce pack ?')) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);
        formData.append('ajax', '1');

        fetch('../../controller/PackController.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                const row = document.getElementById('pack-' + id);
                if (row) {
                    row.remove();
                }
                alert(data.message);
            }
        })
        .catch(err => console.error("Error deleting:", err));
    }
};
