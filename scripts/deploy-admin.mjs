import { readFileSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';
import SftpClient from 'ssh2-sftp-client';

const __dirname = dirname(fileURLToPath(import.meta.url));
const root = join(__dirname, '..');
const config = JSON.parse(readFileSync(join(root, '.vscode', 'sftp.json'), 'utf8'));

const files = [
    'database/migrations/2026_08_19_000001_add_admin_role_and_newsletter_unsubscribe.php',
    'database/migrations/2026_07_22_000001_add_role_and_phone_to_users_table.php',
    'app/Http/Middleware/EnsureUserIsAdmin.php',
    'app/Support/AdminInsights.php',
    'app/Mail/NewsletterBroadcast.php',
    'app/Models/User.php',
    'app/Models/NewsletterSubscriber.php',
    'app/Http/Controllers/NewsletterUnsubscribeController.php',
    'app/Http/Controllers/Admin/DashboardController.php',
    'app/Http/Controllers/Admin/NewsletterController.php',
    'app/Http/Controllers/Admin/OrganizerController.php',
    'app/Http/Controllers/Admin/ParticipantController.php',
    'app/Console/Commands/PromoteAdminCommand.php',
    'bootstrap/app.php',
    'routes/web.php',
    'resources/views/admin/_nav.blade.php',
    'resources/views/admin/dashboard.blade.php',
    'resources/views/admin/newsletter/index.blade.php',
    'resources/views/admin/organizers/index.blade.php',
    'resources/views/admin/participants/index.blade.php',
    'resources/views/emails/newsletter.blade.php',
    'resources/views/newsletter/unsubscribe.blade.php',
    'resources/views/newsletter/unsubscribed.blade.php',
    'resources/views/layouts/navigation.blade.php',
];

const remoteBase = config.remotePath.replace(/\/$/, '');
const sftp = new SftpClient();

await sftp.connect({
    host: config.host,
    port: config.port ?? 22,
    username: config.username,
    password: config.password,
});

for (const rel of files) {
    const remote = `${remoteBase}/${rel}`;
    const remoteDir = remote.substring(0, remote.lastIndexOf('/'));
    try {
        await sftp.mkdir(remoteDir, true);
    } catch {
        // directory may already exist
    }
    await sftp.put(join(root, rel), remote);
    console.log(`OK  ${rel}`);
}

await sftp.end();
console.log('--- Deploy admin panel termine ---');
