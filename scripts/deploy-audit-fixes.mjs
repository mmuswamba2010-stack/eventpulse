import { readFileSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';
import SftpClient from 'ssh2-sftp-client';

const __dirname = dirname(fileURLToPath(import.meta.url));
const root = join(__dirname, '..');
const config = JSON.parse(readFileSync(join(root, '.vscode', 'sftp.json'), 'utf8'));

const files = [
    'config/eventpulse.php',
    'lang/fr.json',
    'lang/en.json',
    'app/Http/Controllers/TicketController.php',
    'app/Http/Controllers/NewsletterController.php',
    'app/Http/Controllers/Admin/OrganizerController.php',
    'app/Http/Controllers/Admin/NewsletterController.php',
    'app/Mail/NewsletterBroadcast.php',
    'app/Notifications/ResetPasswordNotification.php',
    'resources/views/partials/payment-simulation-notice.blade.php',
    'resources/views/events/show.blade.php',
    'resources/views/tickets/show.blade.php',
    'resources/views/admin/_nav.blade.php',
    'resources/views/admin/organizers/index.blade.php',
    'resources/views/organizer/dashboard.blade.php',
    'resources/views/organizer/scan.blade.php',
    'resources/views/organizer/events/index.blade.php',
    'resources/views/organizer/events/create.blade.php',
    'resources/views/organizer/events/edit.blade.php',
    'resources/views/organizer/events/_form.blade.php',
    'resources/views/organizer/events/pay.blade.php',
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
        const local = join(root, rel);
        const remote = `${remoteBase}/${rel.replace(/\\/g, '/')}`;

        try {
            await sftp.mkdir(dirnameRemote(remote), true);
            await sftp.put(local, remote);
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

console.log(`--- Deploy audit fixes: ${ok} ok, ${fail} echecs ---`);
process.exit(fail > 0 ? 1 : 0);

function dirnameRemote(path) {
    const parts = path.split('/');
    parts.pop();
    return parts.join('/') || '/';
}
