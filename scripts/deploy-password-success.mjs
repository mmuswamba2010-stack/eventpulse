import { readFileSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';
import SftpClient from 'ssh2-sftp-client';

const __dirname = dirname(fileURLToPath(import.meta.url));
const root = join(__dirname, '..');
const config = JSON.parse(readFileSync(join(root, '.vscode', 'sftp.json'), 'utf8'));

const files = [
    'routes/auth.php',
    'app/Http/Controllers/Auth/NewPasswordController.php',
    'resources/views/auth/password-reset-success.blade.php',
    'lang/fr.json',
    'lang/en.json',
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
console.log('--- Deploy success page termine ---');
