import { readFileSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';
import SftpClient from 'ssh2-sftp-client';
import { Client } from 'ssh2';

const __dirname = dirname(fileURLToPath(import.meta.url));
const root = join(__dirname, '..');
const config = JSON.parse(readFileSync(join(root, '.vscode', 'sftp.json'), 'utf8'));
const manifest = JSON.parse(readFileSync(join(root, 'public/build/manifest.json'), 'utf8'));

const files = [
    'app/Http/Controllers/EventController.php',
    'app/Http/Controllers/TicketController.php',
    'app/Models/Event.php',
    'app/Support/Money.php',
    'app/Support/PendingTicketCheckout.php',
    'config/eventpulse.php',
    'routes/web.php',
    'lang/fr.json',
    'lang/en.json',
    'resources/views/auth/login.blade.php',
    'resources/views/components/event-card-skeleton.blade.php',
    'resources/views/components/event-card.blade.php',
    'resources/views/components/flash-messages.blade.php',
    'resources/views/components/money.blade.php',
    'resources/views/events/_grid.blade.php',
    'resources/views/events/index.blade.php',
    'resources/views/events/show.blade.php',
    'resources/views/layouts/navigation.blade.php',
    'resources/views/partials/checkout-alerts.blade.php',
    'resources/views/partials/checkout-auth-prompt.blade.php',
    'resources/views/tickets/checkout-confirm.blade.php',
    'resources/views/tickets/choose.blade.php',
    'resources/views/tickets/index.blade.php',
    'public/build/manifest.json',
    `public/build/${manifest['resources/css/app.css'].file}`,
    `public/build/${manifest['resources/js/app.js'].file}`,
];

const remoteBase = config.remotePath.replace(/\/$/, '');
const sftp = new SftpClient();
let ok = 0;
let fail = 0;

try {
    await sftp.connect({
        host: config.host,
        port: config.port ?? 22,
        username: config.username,
        password: config.password,
    });

    for (const rel of files) {
        const remote = `${remoteBase}/${rel.replace(/\\/g, '/')}`;
        const remoteDir = remote.substring(0, remote.lastIndexOf('/'));

        try {
            await sftp.mkdir(remoteDir, true);
            await sftp.put(join(root, rel), remote);
            console.log(`OK  ${rel}`);
            ok++;
        } catch (error) {
            console.log(`FAIL ${rel} - ${error.message}`);
            fail++;
        }
    }
} finally {
    await sftp.end();
}

if (fail > 0) {
    console.log(`--- SFTP: ${ok} ok, ${fail} echecs ---`);
    process.exit(1);
}

const postCommand = [
    'cd ~/www',
    'php artisan route:clear',
    'php artisan config:clear',
    'php artisan view:clear',
    'php artisan cache:clear',
].join(' && ');

await new Promise((resolve, reject) => {
    const conn = new Client();
    conn.on('ready', () => {
        conn.exec(postCommand, (err, stream) => {
            if (err) {
                reject(err);
                return;
            }
            stream.on('close', resolve);
            stream.on('data', (data) => process.stdout.write(data.toString()));
            stream.stderr.on('data', (data) => process.stderr.write(data.toString()));
        });
    });
    conn.on('error', reject);
    conn.connect({
        host: config.host,
        port: config.port ?? 22,
        username: config.username,
        password: config.password,
    });
});

console.log(`--- Deploy checkout & catalogue: ${ok} fichiers, cache vide ---`);
