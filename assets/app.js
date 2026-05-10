import * as Turbo from '@hotwired/turbo';
import './stimulus_bootstrap.js';

import './styles/app.css';

/**
 * Turbo Drive est désactivé : la navigation suit le comportement natif du navigateur
 * (chargement complet du document pour chaque URL). Sans cela, Turbo 8 pouvait mettre
 * à jour l’URL / l’historique tout en laissant le corps de page inchangé visuellement
 * (interaction fragile avec data-turbo-permanent et le shell Dasher).
 */
Turbo.session.drive = false;

if (Turbo?.config?.drive) {
    Turbo.config.drive.progressBarDelay = 35;
}

/**
 * Soumet le formulaire en déclenchant l’événement `submit` (intercepté par Turbo).
 * `HTMLFormElement.submit()` contourne Turbo et provoque un rechargement complet.
 */
function requestTurboCompatibleSubmit(form) {
    if (!(form instanceof HTMLFormElement)) {
        return;
    }
    if (typeof form.requestSubmit === 'function') {
        const submitter = form.querySelector('button[type="submit"], input[type="submit"]');
        if (submitter) {
            form.requestSubmit(submitter);
        } else {
            form.requestSubmit();
        }
    } else {
        form.submit();
    }
}

window.tfRequestFormSubmit = requestTurboCompatibleSubmit;

import Swal from 'sweetalert2';

/**
 * Met à jour les liens actifs de la sidebar / offcanvas après navigation Turbo
 * (le chrome est data-turbo-permanent, les classes « active » serveur ne se mettent pas à jour seules).
 */
function syncNavActiveState() {
    const path = window.location.pathname.replace(/\/$/, '') || '/';

    const matches = (href) => {
        try {
            const u = new URL(href, window.location.origin);
            const linkPath = u.pathname.replace(/\/$/, '') || '/';
            if (path === linkPath) {
                return true;
            }
            if (linkPath !== '/' && path.startsWith(linkPath + '/')) {
                return true;
            }
            return false;
        } catch {
            return false;
        }
    };

    document.querySelectorAll('#miniSidebar a.nav-link[href], #offcanvasExample a.nav-link[href]').forEach((a) => {
        if (a.getAttribute('data-turbo') === 'false') {
            return;
        }
        a.classList.toggle('active', matches(a.href));
    });
}

let deleteFormDelegationBound = false;

/**
 * Confirmations suppression : un seul listener document (compatible Turbo, pas de double bind).
 */
function bindDelegatedDeleteForms() {
    if (deleteFormDelegationBound) {
        return;
    }
    deleteFormDelegationBound = true;
    document.addEventListener(
        'submit',
        (event) => {
            const form = event.target;
            if (!(form instanceof HTMLFormElement)) {
                return;
            }
            const isDelete =
                form.classList.contains('delete-projet-form') ||
                form.classList.contains('delete-tache-form') ||
                form.classList.contains('delete-etiquette-form');
            if (!isDelete) {
                return;
            }
            if (form.dataset.tfDeleteConfirmed === '1') {
                delete form.dataset.tfDeleteConfirmed;
                return;
            }
            event.preventDefault();
            let title = 'Confirmer la suppression ?';
            let text = 'Cette action est irréversible.';
            if (form.classList.contains('delete-projet-form')) {
                title = 'Supprimer ce projet ?';
                text = 'Les tâches associées seront supprimées.';
            } else if (form.classList.contains('delete-tache-form')) {
                title = 'Supprimer cette tâche ?';
                text = '';
            } else if (form.classList.contains('delete-etiquette-form')) {
                title = 'Supprimer cette étiquette ?';
            }
            const proceed = (ok) => {
                if (!ok) {
                    return;
                }
                form.dataset.tfDeleteConfirmed = '1';
                requestTurboCompatibleSubmit(form);
            };
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title,
                    text: text || undefined,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Supprimer',
                    cancelButtonText: 'Annuler',
                }).then((r) => proceed(r.isConfirmed));
            } else {
                proceed(confirm(text));
            }
        },
        true
    );
}

function bindImagePreviewModal() {
    const modalEl = document.getElementById('tfImagePreviewModal');
    if (!modalEl || typeof bootstrap === 'undefined') {
        return;
    }
    const imgEl = document.getElementById('tf-preview-img');
    if (!imgEl) {
        return;
    }
    document.querySelectorAll('[data-tf-image-preview]').forEach((trigger) => {
        if (trigger.dataset.tfPreviewBound) {
            return;
        }
        trigger.dataset.tfPreviewBound = '1';
        trigger.addEventListener('click', (e) => {
            e.preventDefault();
            const src = trigger.getAttribute('data-bs-src') || trigger.getAttribute('href');
            if (!src) {
                return;
            }
            imgEl.src = src;
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        });
    });
}

function closeOpenModalsBeforeCache() {
    if (typeof bootstrap === 'undefined') {
        return;
    }
    document.querySelectorAll('.modal.show').forEach((el) => {
        bootstrap.Modal.getInstance(el)?.hide();
    });
}

function initTaskFlowUi() {
    window.scrollTo(0, 0);
    const main = document.getElementById('tf-main-scroll');
    if (main) {
        main.scrollTop = 0;
    }
    bindDelegatedDeleteForms();
    bindImagePreviewModal();
    syncNavActiveState();
}

document.addEventListener('DOMContentLoaded', initTaskFlowUi);
document.addEventListener('turbo:load', initTaskFlowUi);
document.addEventListener('turbo:before-cache', closeOpenModalsBeforeCache);
