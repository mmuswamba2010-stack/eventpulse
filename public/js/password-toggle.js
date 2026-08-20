(function () {
    'use strict';

    var EYE =
        '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">' +
        '<path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>' +
        '</svg>';

    var EYE_SLASH =
        '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">' +
        '<path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12c1.292 4.338 5.31 7.5 10.066 7.5.993 0 1.952-.138 2.863-.395m-2.032-2.033A10.45 10.45 0 0112 4.5c-2.23 0-4.283.698-5.964 1.886M15 12a3 3 0 11-6 0 3 3 0 016 0zm-9.75 9.75l14.5-14.5"/>' +
        '</svg>';

    function bindToggle(input, button, showIcon, hideIcon) {
        if (button.dataset.epBound === 'true') {
            return;
        }

        button.dataset.epBound = 'true';
        input.type = 'password';
        showIcon.style.display = 'inline-flex';
        hideIcon.style.display = 'none';

        button.addEventListener('click', function () {
            var visible = input.type === 'text';

            input.type = visible ? 'password' : 'text';
            showIcon.style.display = visible ? 'inline-flex' : 'none';
            hideIcon.style.display = visible ? 'none' : 'inline-flex';
            button.setAttribute('aria-pressed', visible ? 'false' : 'true');
            button.setAttribute(
                'aria-label',
                visible ? 'Afficher le mot de passe' : 'Masquer le mot de passe'
            );
        });
    }

    function patchLegacyAlpineWrapper(wrapper) {
        if (wrapper.dataset.epLegacyPatched === 'true') {
            return;
        }

        var input = wrapper.querySelector('input');
        var button = wrapper.querySelector('button[type="button"]');

        if (!input || !button) {
            return;
        }

        wrapper.dataset.epLegacyPatched = 'true';
        wrapper.removeAttribute('x-data');
        input.removeAttribute('x-bind:type');
        input.type = 'password';

        button.removeAttribute('@click');
        button.classList.add('ep-password-toggle', 'z-20');
        button.style.color = '#8A8F98';

        var showIcon = document.createElement('span');
        showIcon.className = 'inline-flex';
        showIcon.innerHTML = EYE;

        var hideIcon = document.createElement('span');
        hideIcon.className = 'inline-flex';
        hideIcon.style.display = 'none';
        hideIcon.innerHTML = EYE_SLASH;

        button.innerHTML = '';
        button.appendChild(showIcon);
        button.appendChild(hideIcon);

        bindToggle(input, button, showIcon, hideIcon);
    }

    function bindModernField(field) {
        if (field.dataset.bound === 'true') {
            return;
        }

        var input = field.querySelector('input');
        var button = field.querySelector('[data-password-toggle]');
        var showIcon = field.querySelector('[data-password-icon-show]');
        var hideIcon = field.querySelector('[data-password-icon-hide]');

        if (!input || !button || !showIcon || !hideIcon) {
            return;
        }

        field.dataset.bound = 'true';
        bindToggle(input, button, showIcon, hideIcon);
    }

    function enhancePasswordFields() {
        document.querySelectorAll('[data-password-field]').forEach(bindModernField);

        document.querySelectorAll('[x-data] input[x-bind\\:type]').forEach(function (input) {
            var wrapper = input.closest('[x-data]');

            if (wrapper) {
                patchLegacyAlpineWrapper(wrapper);
            }
        });

        document.querySelectorAll('input[type="password"]').forEach(function (input) {
            if (input.closest('[data-password-field]') || input.closest('[data-ep-legacy-patched]')) {
                return;
            }

            var wrapper = input.parentElement;

            if (!wrapper || wrapper.querySelector('[data-password-toggle]')) {
                return;
            }

            wrapper.classList.add('relative', 'w-full', 'ep-password-field');
            wrapper.dataset.epLegacyPatched = 'true';
            input.classList.add('pr-11');

            var button = document.createElement('button');
            button.type = 'button';
            button.dataset.passwordToggle = 'true';
            button.className =
                'ep-password-toggle absolute inset-y-0 right-0 z-20 flex h-full items-center px-3.5';
            button.style.color = '#8A8F98';
            button.setAttribute('aria-label', 'Afficher le mot de passe');
            button.setAttribute('aria-pressed', 'false');

            var showIcon = document.createElement('span');
            showIcon.dataset.passwordIconShow = 'true';
            showIcon.className = 'inline-flex';
            showIcon.innerHTML = EYE;

            var hideIcon = document.createElement('span');
            hideIcon.dataset.passwordIconHide = 'true';
            hideIcon.className = 'inline-flex';
            hideIcon.style.display = 'none';
            hideIcon.innerHTML = EYE_SLASH;

            button.appendChild(showIcon);
            button.appendChild(hideIcon);
            wrapper.appendChild(button);

            bindToggle(input, button, showIcon, hideIcon);
        });
    }

    function run() {
        enhancePasswordFields();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', run);
    } else {
        run();
    }

    document.addEventListener('alpine:initialized', run);
    setTimeout(run, 250);
    setTimeout(run, 1000);
})();
