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
    'bootstrap/app.php',
    'routes/web.php',
    'database/migrations/2026_07_27_000001_add_platform_payments_moderation_soft_deletes.php',
    'database/migrations/2026_07_25_000010_add_ticket_number_to_tickets_table.php',
    'app/Models/Payment.php',
    'app/Models/User.php',
    'app/Models/Event.php',
    'app/Models/Ticket.php',
    'app/Services/MobileMoneyPaymentService.php',
    'app/Policies/EventPolicy.php',
    'app/Policies/TicketPolicy.php',
    'app/Policies/UserPolicy.php',
    'app/Http/Controllers/Controller.php',
    'app/Http/Controllers/TicketController.php',
    'app/Http/Controllers/PaymentStatusController.php',
    'app/Http/Controllers/Webhooks/MobileMoneyWebhookController.php',
    'app/Http/Controllers/Organizer/EventController.php',
    'app/Http/Controllers/Organizer/PendingController.php',
    'app/Http/Controllers/Organizer/SuspendedController.php',
    'app/Http/Controllers/Admin/OrganizerController.php',
    'app/Http/Controllers/Auth/RegisteredUserController.php',
    'app/Http/Controllers/Auth/AuthenticatedSessionController.php',
    'app/Http/Middleware/EnsureOrganizerIsApproved.php',
    'app/Http/Middleware/EnsureUserIsOrganizer.php',
    'app/Http/Middleware/VerifyMobileMoneyWebhook.php',
    'resources/views/payments/show.blade.php',
    'resources/views/organizer/pending.blade.php',
    'resources/views/organizer/suspended.blade.php',
    'resources/views/organizer/dashboard.blade.php',
    'resources/views/organizer/events/index.blade.php',
    'resources/views/organizer/scan.blade.php',
    'resources/views/admin/_nav.blade.php',
    'resources/views/admin/dashboard.blade.php',
    'resources/views/admin/organizers/index.blade.php',
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

console.log(`--- Deploy platform features: ${ok} ok, ${fail} echecs ---`);
process.exit(fail > 0 ? 1 : 0);

function dirnameRemote(path) {
    const parts = path.split('/');
    parts.pop();
    return parts.join('/') || '/';
}
