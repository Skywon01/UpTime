// On utilise 'turbo:load' qui s'exécute au premier chargement ET à chaque navigation
document.addEventListener('turbo:load', () => {
    const container = document.querySelector('#parts-container');
    const addButton = document.querySelector('.add-part-btn');
    const machineSelect = document.querySelector('select[name*="[machine]"]');

    // Sécurité : on n'exécute le code que si on est sur la bonne page
    if (!container || !addButton || !machineSelect) return;

    const prototype = container.dataset.prototype;
    let index = container.querySelectorAll('.part-row').length;

    const updatePartSelect = async (selectElement) => {
        const machineId = machineSelect.value;
        if (!machineId) {
            selectElement.innerHTML = '<option value="">Sélectionnez d\'abord une machine</option>';
            return;
        }

        try {
            const response = await fetch(`/part/compatible/${machineId}`);
            const parts = await response.json();

            selectElement.innerHTML = '<option value="">Choisir une pièce...</option>';
            /** @param {{id: number, designation: string}} part */
            parts.forEach(part => {
                const option = new Option(part.designation, part.id);
                selectElement.add(option);
            });
        } catch (error) {
            console.error('Erreur:', error);
        }
    };

    // Rafraîchir les selects existants au changement de machine
    machineSelect.addEventListener('change', () => {
        container.querySelectorAll('select[name*="[part]"]').forEach(select => updatePartSelect(select));
    });

    // Ajouter une ligne
    addButton.addEventListener('click', async () => {
        const newForm = prototype.replace(/__name__/g, index);
        const row = document.createElement('div');
        row.classList.add('part-row', 'card', 'p-3', 'mb-3', 'bg-light');
        row.innerHTML = `
            ${newForm}
            <button type="button" class="btn btn-danger btn-sm mt-2 remove-part-btn">
                <i class="bi bi-trash"></i> Supprimer cette pièce
            </button>
        `;
        container.appendChild(row);

        const newSelect = row.querySelector('select[name*="[part]"]');
        if (newSelect) {
            await updatePartSelect(newSelect);
        }
        index++;
    });

    // Suppression
    container.addEventListener('click', (e) => {
        const btn = e.target.closest('.remove-part-btn');
        if (btn) btn.closest('.part-row').remove();
    });
    if (machineSelect.value) {
        container.querySelectorAll('select[name*="[part]"]').forEach(select => updatePartSelect(select));
    }
});
