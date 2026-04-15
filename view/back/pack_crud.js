/* Javascript CRUD for Dashboard Packs */

document.addEventListener('DOMContentLoaded', () => {
    loadPacks();

    const form = document.getElementById('packForm');
    if (form) {
    form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Controle de saisie natively done by "required" and "type" in HTML,
            // but we add manual JS validation as well to respect instruction:
            const nom = document.getElementById('nom').value.trim();
            const prix = parseFloat(document.getElementById('prix').value);
            const duree = document.getElementById('duree').value;
            const description = document.getElementById('description').value.trim();
            const nb = parseInt(document.getElementById('nb').value);
            const support = document.getElementById('support').value;

            if (!nom) { alert("Le nom est obligatoire"); return; }
            if (isNaN(prix) || prix < 0) { alert("Le prix doit être un nombre positif"); return; }
            if (!duree) { alert("La durée (date) est obligatoire"); return; }
            if (!description) { alert("La description est obligatoire"); return; }
            if (isNaN(nb) || nb < 0) { alert("Le nombre de projets doit être un entier positif"); return; }
            if (support !== 'oui' && support !== 'non') { alert("Veuillez sélectionner le support prioritaire"); return; }

            const fd = new FormData(form);
            fd.append('ajax', '1');
            
            try {
                const res = await fetch('../../controller/PackController.php', {
                    method: 'POST',
                    body: fd,
                    credentials: 'same-origin'
                });

                const text = await res.text();
                let data = null;
                try { data = text ? JSON.parse(text) : null; } catch (e) { data = null; }

                if (!res.ok) {
                    console.error('Server error', res.status, text);
                    alert((data && data.message) ? data.message : 'Erreur serveur.');
                    return;
                }

                if (data && data.status === 'success') {
                    alert(data.message);
                    form.reset();
                    document.getElementById('action').value = 'add'; // Reset to add mode
                    document.getElementById('id-pack').value = '';
                    loadPacks(); // Reload table dynamically
                } else {
                    alert('Erreur: ' + ((data && data.message) ? data.message : 'Réponse inattendue'));
                }
            } catch (err) {
                console.error('Erreur Fetch:', err);
                alert('Erreur réseau. Veuillez réessayer.');
            }
        });
    }
});

async function loadPacks() {
    try {
        const res = await fetch('../../controller/PackController.php?action=getAll', { credentials: 'same-origin' });
        const text = await res.text();
        let data = null;
        try { data = text ? JSON.parse(text) : []; } catch (e) { data = []; }

        if (!res.ok) {
            console.error('Failed to load packs', res.status, text);
            showLoadError();
            return;
        }

        const tbody = document.querySelector('#packs-table tbody');
        document.getElementById('count-packs').innerText = Array.isArray(data) ? data.length : 0;

        // Build rows safely using DOM APIs to avoid injection issues
        tbody.innerHTML = '';
        (data || []).forEach(p => {
            const tr = document.createElement('tr');
            tr.id = `pack-${p['id-pack']}`;

            const tdId = document.createElement('td'); tdId.textContent = p['id-pack']; tr.appendChild(tdId);
            const tdNom = document.createElement('td'); tdNom.style.fontWeight = 'bold'; tdNom.textContent = p['nom-pack']; tr.appendChild(tdNom);
            const tdPrix = document.createElement('td'); tdPrix.style.color = 'var(--green-card)'; tdPrix.style.fontWeight = 'bold'; tdPrix.textContent = `${p.prix} dt`; tr.appendChild(tdPrix);
            const tdNb = document.createElement('td'); tdNb.textContent = `${p['nb-proj-max']} Max`; tr.appendChild(tdNb);

            const tdActions = document.createElement('td');

            const btnEdit = document.createElement('button');
            btnEdit.className = 'btn-sm btn-edit';
            btnEdit.textContent = 'Modifier';
            // store pack data safely as JSON in dataset
            btnEdit.dataset.pack = JSON.stringify(p);
            btnEdit.addEventListener('click', () => {
                try { editPack(JSON.parse(btnEdit.dataset.pack)); } catch (e) { console.error('Invalid pack data', e); }
            });

            const btnDelete = document.createElement('button');
            btnDelete.className = 'btn-sm btn-delete';
            btnDelete.textContent = 'Supprimer';
            btnDelete.addEventListener('click', () => deletePack(p['id-pack']));

            tdActions.appendChild(btnEdit);
            tdActions.appendChild(btnDelete);
            tr.appendChild(tdActions);

            tbody.appendChild(tr);
        });
    } catch (err) {
        console.error('Error loading packs:', err);
        showLoadError();
    }
}

function showLoadError() {
    alert('Erreur lors du chargement des packs.');
}

// Global functions for inline HTML onclick handlers
window.deletePack = async function(id) {
    if(!confirm("Supprimer ce pack définitivement ? Cette action est irréversible.")) return;

    const fd = new FormData();
    fd.append('action', 'delete');
    fd.append('id', id);
    fd.append('ajax', '1');

    try {
        const res = await fetch('../../controller/PackController.php', { method: 'POST', body: fd, credentials: 'same-origin' });
        const text = await res.text();
        let data = null;
        try { data = text ? JSON.parse(text) : null; } catch (e) { data = null; }

        if (!res.ok) {
            console.error('Delete failed', res.status, text);
            alert((data && data.message) ? data.message : 'Erreur serveur lors de la suppression');
            return;
        }

        if (data && data.status === 'success') {
            const row = document.getElementById('pack-' + id);
            if (row) row.remove();
            let count = parseInt(document.getElementById('count-packs').innerText) || 0;
            document.getElementById('count-packs').innerText = Math.max(0, count - 1);
        } else {
            alert((data && data.message) ? data.message : 'Erreur lors de la suppression');
        }
    } catch (err) {
        console.error(err);
        alert('Erreur réseau. Veuillez réessayer.');
    }
};

window.editPack = function(p) {
    // Populate form and change mode to update
    document.getElementById('action').value = 'update';
    document.getElementById('id-pack').value = p['id-pack'];
    document.getElementById('nom').value = p['nom-pack'];
    document.getElementById('prix').value = p['prix'];
    document.getElementById('duree').value = p['duree'];
    document.getElementById('description').value = p['description'];
    document.getElementById('nb').value = p['nb-proj-max'];
    document.getElementById('support').value = p['support-prioritaire'];
    
    window.scrollTo({ top: 0, behavior: 'smooth' });
};
