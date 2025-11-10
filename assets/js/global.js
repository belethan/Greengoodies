// assets/js/global.js
document.addEventListener('DOMContentLoaded', () => {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.classList.add('fade-out');
            setTimeout(() => alert.remove(), 1000);
        }, 5000);
    });

    // === Gestion du modal de confirmation global ===
    const confirmModal = document.getElementById('confirmModal');
    const confirmMessage = document.getElementById('confirmModalMessage');
    const confirmYesBtn = document.getElementById('confirmModalYesBtn');

    let confirmCallback = null;

    // Fonction globale d'ouverture
    window.openConfirmModal = function (message, onConfirm) {
        confirmMessage.textContent = message || "Confirmez-vous cette action ?";
        confirmCallback = onConfirm;
        const modal = new bootstrap.Modal(confirmModal);
        modal.show();
    };

    // Clic sur bouton "Confirmer"
    confirmYesBtn.addEventListener('click', () => {
        if (typeof confirmCallback === 'function') {
            confirmCallback();
        }
        const modal = bootstrap.Modal.getInstance(confirmModal);
        modal.hide();
    });

    // Détection automatique des liens avec data-confirm
    document.querySelectorAll('a[data-confirm]').forEach(link => {
        link.addEventListener('click', function (event) {
            event.preventDefault(); // bloque la redirection directe
            const message = this.getAttribute('data-confirm');
            const href = this.getAttribute('href');
            openConfirmModal(message, () => {
                window.location.href = href;
            });
        });
    });


});
