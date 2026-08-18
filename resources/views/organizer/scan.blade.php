<x-app-layout>
    <x-slot name="header">
        <h2 class="flex items-center gap-2 font-extrabold text-2xl text-slate-900 tracking-tight">
            <x-icon name="camera" class="w-7 h-7 text-brand" />
            Scanner de billets
        </h2>
        <p class="mt-1 text-sm text-slate-500">Présentez le QR Code du billet devant la caméra pour valider l'entrée.</p>
    </x-slot>

    <div class="py-8 pb-16">
        <div class="max-w-lg mx-auto sm:px-6 lg:px-8 px-4 space-y-6">
            <!-- Zone caméra -->
            <div class="relative bg-slate-950 rounded-3xl shadow-2xl shadow-slate-400/20 overflow-hidden">
                <div class="pointer-events-none absolute inset-0 z-10 bg-grid opacity-10"></div>

                <div id="qr-reader" class="w-full aspect-square [&_video]:object-cover [&_video]:w-full [&_video]:h-full"></div>

                <!-- Cadre de visée décoratif -->
                <div class="pointer-events-none absolute inset-0 z-10 flex items-center justify-center">
                    <div class="relative w-56 h-56">
                        <span class="absolute -top-1 -left-1 w-9 h-9 border-t-4 border-l-4 border-brand rounded-tl-2xl"></span>
                        <span class="absolute -top-1 -right-1 w-9 h-9 border-t-4 border-r-4 border-brand rounded-tr-2xl"></span>
                        <span class="absolute -bottom-1 -left-1 w-9 h-9 border-b-4 border-l-4 border-brand rounded-bl-2xl"></span>
                        <span class="absolute -bottom-1 -right-1 w-9 h-9 border-b-4 border-r-4 border-brand rounded-br-2xl"></span>
                    </div>
                </div>

                <div class="absolute top-4 left-4 z-20 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/10 backdrop-blur text-white text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> En direct
                </div>
            </div>
            <p class="text-xs text-slate-400 text-center -mt-2">
                Autorisez l'accès à la caméra, puis alignez le QR Code dans le cadre.
            </p>

            <!-- Résultat du scan -->
            <div id="scan-result" class="hidden rounded-3xl shadow-lg p-7 text-center border-2">
                <p id="scan-icon" class="flex justify-center"></p>
                <p id="scan-message" class="mt-3 font-bold text-lg"></p>
                <div id="scan-details" class="mt-2 text-sm"></div>
                <button id="scan-again"
                        class="mt-5 inline-flex items-center gap-1.5 px-5 py-2.5 bg-gradient-to-r bg-brand hover:bg-brand-700 text-white text-sm font-semibold rounded-full  hover:brightness-110 transition">
                    <x-icon name="camera" class="w-4 h-4" /> Scanner le billet suivant
                </button>
            </div>

            <!-- Saisie manuelle -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
                <h3 class="flex items-center gap-2 font-bold text-slate-800 text-sm">
                    <x-icon name="identification" class="w-4 h-4 text-brand" /> Saisie manuelle du code
                </h3>
                <form id="manual-form" class="mt-3 flex gap-2">
                    <input type="text" id="manual-code" placeholder="Code du billet..."
                           class="flex-1 border-slate-200 bg-slate-50/60 focus:bg-white focus:border-brand focus:ring-brand/40 rounded-xl shadow-sm text-sm placeholder:text-slate-400">
                    <button type="submit"
                            class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-sm font-semibold rounded-xl transition">
                        Vérifier
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const resultBox = document.getElementById('scan-result');
            const iconEl = document.getElementById('scan-icon');
            const messageEl = document.getElementById('scan-message');
            const detailsEl = document.getElementById('scan-details');
            const againBtn = document.getElementById('scan-again');

            let scanner = new Html5Qrcode('qr-reader');
            let scanning = false;
            let processing = false;

            // Icônes SVG (Heroicons outline) affichées selon le résultat du scan.
            const svgIcon = (path, colorClass) =>
                `<svg class="w-16 h-16 ${colorClass}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="${path}" /></svg>`;

            const icons = {
                'check-circle': 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                'shield-exclamation': 'M12 9v3.75m0 3.75h.008v.008H12v-.008zM12 2.714a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.398-.239-2.74-.678-3.985a11.955 11.955 0 01-8.618-3.04z',
                'x-circle': 'M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                'exclamation-triangle': 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z',
            };

            const styles = {
                success:      { box: 'bg-emerald-50 border-emerald-300',  text: 'text-emerald-700', icon: svgIcon(icons['check-circle'], 'text-emerald-600') },
                already_used: { box: 'bg-rose-50 border-rose-400',        text: 'text-rose-700',    icon: svgIcon(icons['shield-exclamation'], 'text-rose-600') },
                invalid:      { box: 'bg-rose-50 border-rose-300',        text: 'text-rose-700',    icon: svgIcon(icons['x-circle'], 'text-rose-600') },
                error:        { box: 'bg-amber-50 border-amber-300',      text: 'text-amber-700',   icon: svgIcon(icons['exclamation-triangle'], 'text-amber-600') },
            };

            function escapeHtml(value) {
                return String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            }

            function showResult(result, message, ticket) {
                const s = styles[result] ?? styles.error;
                resultBox.className = 'rounded-3xl shadow-lg p-7 text-center border-2 ' + s.box;
                iconEl.innerHTML = s.icon;
                messageEl.className = 'mt-3 font-bold text-lg ' + s.text;
                messageEl.textContent = message;
                if (ticket) {
                    detailsEl.innerHTML =
                        `<p class="text-slate-700"><strong>${escapeHtml(ticket.holder)}</strong> — ${escapeHtml(ticket.event)}<br>${escapeHtml(ticket.event_date)}</p>`;
                } else {
                    detailsEl.textContent = '';
                }
                resultBox.classList.remove('hidden');

                if (result === 'already_used') {
                    // Alerte anti-fraude : vibration si supportée.
                    navigator.vibrate?.([200, 100, 200]);
                }
            }

            async function validateCode(code) {
                if (processing) return;
                processing = true;

                try {
                    const response = await fetch('{{ route('organizer.scan.validate') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({ ticket_code: code }),
                    });

                    const data = await response.json();
                    showResult(data.result ?? 'error', data.message ?? 'Erreur inattendue.', data.ticket ?? null);
                } catch (e) {
                    showResult('error', 'Erreur de connexion au serveur. Réessayez.');
                } finally {
                    processing = false;
                }
            }

            async function startScanner() {
                resultBox.classList.add('hidden');
                if (scanning) return;

                try {
                    await scanner.start(
                        { facingMode: 'environment' },
                        { fps: 10, qrbox: { width: 250, height: 250 } },
                        async (decodedText) => {
                            await stopScanner();
                            await validateCode(decodedText);
                        }
                    );
                    scanning = true;
                } catch (e) {
                    showResult('error', "Impossible d'accéder à la caméra. Vérifiez les autorisations du navigateur.");
                }
            }

            async function stopScanner() {
                if (!scanning) return;
                scanning = false;
                try { await scanner.stop(); } catch (e) { /* déjà arrêté */ }
            }

            againBtn.addEventListener('click', startScanner);

            document.getElementById('manual-form').addEventListener('submit', async (e) => {
                e.preventDefault();
                const code = document.getElementById('manual-code').value.trim();
                if (!code) return;
                await stopScanner();
                await validateCode(code);
                document.getElementById('manual-code').value = '';
            });

            startScanner();
        });
    </script>
</x-app-layout>
