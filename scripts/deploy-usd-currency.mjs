import { readFileSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';
import SftpClient from 'ssh2-sftp-client';

const __dirname = dirname(fileURLToPath(import.meta.url));
const root = join(__dirname, '..');
const config = JSON.parse(readFileSync(join(root, '.vscode', 'sftp.json'), 'utf8'));

const files = [
    'config/eventpulse.php',
    'app/Support/Money.php',
    'resources/views/components/money.blade.php',
    'resources/views/organizer/dashboard.blade.php',
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
        await sftp.put(join(root, rel), remote);
        console.log(`OK  ${rel}`);
    }
} finally {
    await sftp.end();
}

console.log('--- Deploy USD currency: done ---');
