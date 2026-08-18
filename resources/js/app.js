import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.data('eventCatalog', ({ gridUrl, page = 1, initialTotal = 0 }) => ({
        loading: false,
        html: '',
        total: initialTotal,
        gridUrl,
        page,

        hydrate() {
            // Premier affichage = contenu serveur déjà visible (pas de skeleton bloquant).
            this.loading = false;
        },

        async load() {
            this.loading = true;
            try {
                const url = new URL(this.gridUrl, window.location.origin);
                if (this.page > 1) {
                    url.searchParams.set('page', String(this.page));
                }
                const response = await fetch(url.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        Accept: 'text/html',
                    },
                });
                if (! response.ok) {
                    throw new Error('Impossible de charger les événements.');
                }
                this.html = await response.text();
                this.total = this.extractTotal(this.html);
            } catch (e) {
                this.html = '<div class="bg-white rounded-3xl border border-rose-200 text-rose-700 p-8 text-center text-sm font-medium">Impossible de charger le catalogue. Rechargez la page.</div>';
            } finally {
                this.loading = false;
            }
        },

        extractTotal(markup) {
            const match = markup.match(/data-total="(\d+)"/);
            return match ? Number(match[1]) : this.total;
        },

        onGridClick(event) {
            const link = event.target.closest('a');
            if (! link) return;

            try {
                const url = new URL(link.getAttribute('href') || '', window.location.origin);
                if (url.pathname !== '/' && url.pathname !== new URL(window.location.href).pathname) {
                    return;
                }
                if (! url.searchParams.has('page')) {
                    return;
                }

                event.preventDefault();
                this.page = Number(url.searchParams.get('page') || 1);
                const next = new URL(window.location.href);
                if (this.page > 1) next.searchParams.set('page', String(this.page));
                else next.searchParams.delete('page');
                window.history.pushState({}, '', next);
                this.load();
                this.$el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } catch (_) {
                // laisser le navigateur gérer
            }
        },
    }));
});

Alpine.start();
