document.addEventListener('DOMContentLoaded', () => {
    const modal = document.querySelector('[data-booking-modal]');
    if (!modal) return;

    const sections = {
        pause: modal.querySelector('[data-mode-section="pause"]'),
        resume: modal.querySelector('[data-mode-section="resume"]'),
    };

    const closureField = modal.querySelector('[data-closure-field]');

    const showModal = (mode) => {
        if (!modal) return;

        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');

        Object.entries(sections).forEach(([key, element]) => {
            if (!element) return;
            element.classList.toggle('hidden', key !== mode);
        });

        if (mode === 'resume' && closureField) {
            closureField.closest('form')?.classList.remove('hidden');
        }
    };

    const hideModal = () => {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    };

    document.querySelectorAll('[data-action="toggle-booking"]').forEach((button) => {
        button.addEventListener('click', () => {
            const mode = button.dataset.mode ?? 'pause';
            if (mode === 'resume' && closureField) {
                closureField.value = button.dataset.closureId ?? '';
            }
            showModal(mode);
        });
    });

    modal.querySelectorAll('[data-close-booking]').forEach((button) => {
        button.addEventListener('click', hideModal);
    });

    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            hideModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            hideModal();
        }
    });
});