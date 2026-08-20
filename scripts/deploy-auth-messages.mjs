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
    'app/Http/Controllers/Auth/PasswordController.php',
    'app/Http/Controllers/Auth/AuthenticatedSessionController.php',
    'app/Http/Controllers/Auth/RegisteredUserController.php',
    'app/Http/Controllers/ProfileController.php',
    'resources/views/components/flash-messages.blade.php',
    'resources/views/profile/partials/update-password-form.blade.php',
    'resources/views/profile/partials/update-profile-information-form.blade.php',
    'app/Http/Controllers/Auth/AuthenticatedSessionController.php',
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
    await sftp.put(join(root, rel), `${remoteBase}/${rel}`);
    console.log(`OK  ${rel}`);
}

await sftp.end();
console.log('--- Deploy auth messages termine ---');
