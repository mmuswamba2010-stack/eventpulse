import { readFileSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';
import SftpClient from 'ssh2-sftp-client';
import { Client } from 'ssh2';

const __dirname = dirname(fileURLToPath(import.meta.url));
const root = join(__dirname, '..');
const config = JSON.parse(readFileSync(join(root, '.vscode', 'sftp.json'), 'utf8'));

const files = [
    'composer.json',
    'composer.lock',
    'app/Support/SocialAuth.php',
    'app/Http/Controllers/Auth/SocialAuthController.php',
    'app/Models/User.php',
    'app/Providers/AppServiceProvider.php',
    'config/services.php',
    'database/migrations/2026_09_09_000001_add_social_auth_to_users_table.php',
    'routes/auth.php',
    'resources/views/partials/social-auth-buttons.blade.php',
    'resources/views/auth/login.blade.php',
    'resources/views/auth/register-participant.blade.php',
    'resources/views/auth/register-organizer.blade.php',
    'resources/views/auth/register-organizer-complete.blade.php',
    'lang/fr.json',
    'lang/en.json',
    '.env.example',
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
    'composer install --no-dev --optimize-autoloader --no-interaction',
    'php artisan migrate --force',
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
            stream.on('close', (code) => (code === 0 ? resolve() : reject(new Error(`Remote command exited with ${code}`))));
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

console.log('--- Deploy social auth: done ---');
