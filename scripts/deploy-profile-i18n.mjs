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
    'resources/views/profile/edit.blade.php',
    'resources/views/profile/partials/update-profile-information-form.blade.php',
    'resources/views/profile/partials/update-password-form.blade.php',
    'resources/views/profile/partials/delete-user-form.blade.php',
    'resources/views/components/modal.blade.php',
    'resources/views/components/secondary-button.blade.php',
    'resources/views/layouts/app.blade.php',
    'resources/views/auth/verify-email.blade.php',
    'resources/views/auth/confirm-password.blade.php',
    'resources/views/tickets/index.blade.php',
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
console.log('--- Deploy profile i18n/dark termine ---');
