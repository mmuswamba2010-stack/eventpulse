import { readFileSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';
import SftpClient from 'ssh2-sftp-client';

const __dirname = dirname(fileURLToPath(import.meta.url));
const root = join(__dirname, '..');
const config = JSON.parse(readFileSync(join(root, '.vscode', 'sftp.json'), 'utf8'));

const files = [
    'lang/fr.json',
    'lang/en.json',
    'lang/fr/passwords.php',
    'app/Http/Middleware/SetLocale.php',
    'app/Http/Controllers/LocaleController.php',
    'app/Http/Controllers/Auth/NewPasswordController.php',
    'app/Models/User.php',
    'app/Notifications/ResetPasswordNotification.php',
    'app/Console/Commands/MailTestCommand.php',
    'bootstrap/app.php',
    'routes/web.php',
    'routes/auth.php',
    'config/eventpulse.php',
    'config/mail.php',
    'resources/views/partials/theme-init.blade.php',
    'resources/views/components/locale-theme-toggle.blade.php',
    'resources/views/layouts/app.blade.php',
    'resources/views/layouts/guest.blade.php',
    'resources/views/layouts/navigation.blade.php',
    'resources/views/auth/login.blade.php',
    'resources/views/auth/register.blade.php',
    'resources/views/auth/forgot-password.blade.php',
    'resources/views/auth/reset-password.blade.php',
    'resources/views/auth/password-reset-success.blade.php',
    'resources/views/components/input-label.blade.php',
    'resources/views/components/auth-session-status.blade.php',
    'resources/views/events/index.blade.php',
    'resources/views/events/show.blade.php',
    'resources/views/organizer/events/_form.blade.php',
    'resources/views/organizer/events/pay.blade.php',
    'resources/views/components/nav-link.blade.php',
    'resources/views/components/responsive-nav-link.blade.php',
    'resources/views/components/event-card.blade.php',
    'resources/views/components/category-tile.blade.php',
    'resources/views/components/brand-logo.blade.php',
    'public/build/manifest.json',
    'public/build/assets/app-Cj81_60J.css',
    'public/build/assets/app-mYZXg42s.js',
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

console.log(`--- Termine: ${ok} ok, ${fail} echecs ---`);
process.exit(fail > 0 ? 1 : 0);

function dirnameRemote(path) {
    const parts = path.split('/');
    parts.pop();
    return parts.join('/') || '/';
}
