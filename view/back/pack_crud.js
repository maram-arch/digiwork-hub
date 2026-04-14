/* Javascript CRUD for Dashboard Packs */

document.addEventListener('DOMContentLoaded', () => {
    loadPacks();

    const form = document.getElementById('packForm');
    if (form) {
        form.addEventListener('submit', (e) => {
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
            
            fetch('../../controller/PackController.php', {
                method: 'POST',
                body: fd
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message);
                    form.reset();
                    document.getElementById('action').value = 'add'; // Reset to add mode
                    document.getElementById('id-pack').value = '';
                    loadPacks(); // Reload table dynamically
                } else {
                    alert("Erreur: " + data.message);
                }
            })
            .catch(err => console.error("Erreur Fetch:", err));
        });
    }
});

function loadPacks() {
    fetch('../../controller/PackController.php?action=getAll')
    .then(res => res.json())
    .then(data => {
        const tbody = document.querySelector('#packs-table tbody');
        document.getElementById('count-packs').innerText = data.length;
        
        let html = '';
        data.forEach(p => {
            html += `
                <tr id="pack-${p['id-pack']}">
                    <td>${p['id-pack']}</td>
                    <td style="font-weight:bold;">${p['nom-pack']}</td>
                    <td style="color:var(--green-card); font-weight:bold;">${p.prix} dt</td>
                    <td>${p['nb-proj-max']} Max</td>
                    <td>
                        <button class="btn-sm btn-edit" onclick='editPack(${JSON.stringify(p)})'>Modifier</button>
                        <button class="btn-sm btn-delete" onclick="deletePack(${p['id-pack']})">Supprimer</button>
                    </td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
    })
    .catch(err => console.error(err));
}

// Global functions for inline HTML onclick handlers
window.deletePack = function(id) {
    if(!confirm("Supprimer ce pack définitivement ?")) return;
    
    const fd = new FormData();
    fd.append('action', 'delete');
    fd.append('id', id);
    fd.append('ajax', '1');

    fetch('../../controller/PackController.php', {
        method: 'POST',
        body: fd
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success') {
            document.getElementById('pack-' + id).remove();
            let count = parseInt(document.getElementById('count-packs').innerText);
            document.getElementById('count-packs').innerText = count - 1;
        }
    })
    .catch(err => console.error(err));
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
