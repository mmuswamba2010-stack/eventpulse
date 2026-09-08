import { readFileSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';
import SftpClient from 'ssh2-sftp-client';
import { Client } from 'ssh2';

const __dirname = dirname(fileURLToPath(import.meta.url));
const root = join(__dirname, '..');
const config = JSON.parse(readFileSync(join(root, '.vscode', 'sftp.json'), 'utf8'));

const files = [
    'config/eventpulse.php',
    'bootstrap/app.php',
    'routes/web.php',
    'database/migrations/2026_09_07_000001_add_organizer_terms_to_users_table.php',
    'app/Support/OrganizerTerms.php',
    'app/Http/Middleware/EnsureOrganizerTermsAccepted.php',
    'app/Http/Controllers/LegalController.php',
    'app/Http/Controllers/Organizer/TermsController.php',
    'app/Http/Controllers/Auth/RegisteredUserController.php',
    'app/Models/User.php',
    'resources/views/legal/_organizer-terms-content.blade.php',
    'resources/views/legal/organizer-terms.blade.php',
    'resources/views/organizer/terms/accept.blade.php',
    'resources/views/auth/register.blade.php',
    'resources/views/layouts/app.blade.php',
    'lang/fr.json',
    'lang/en.json',
];

const remoteBase = config.remotePath.replace(/\/$/, '');
const sftp = new SftpClient();

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
        await sftp.mkdir(remoteDir, true);
        await sftp.put(join(root, rel), remote);
        console.log(`OK  ${rel}`);
    }
} finally {
    await sftp.end();
}

const postCommand = [
    'cd ~/www',
    'php artisan migrate --force',
    'php artisan config:clear',
    'php artisan route:clear',
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

console.log('--- Deploy organizer terms: done ---');
