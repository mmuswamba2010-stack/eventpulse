import { readFileSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';
import SftpClient from 'ssh2-sftp-client';

const __dirname = dirname(fileURLToPath(import.meta.url));
const root = join(__dirname, '..');
const config = JSON.parse(readFileSync(join(root, '.vscode', 'sftp.json'), 'utf8'));

const file = 'public/google0191f7510dbc925c.html';
const remoteBase = config.remotePath.replace(/\/$/, '');
const sftp = new SftpClient();

try {
    await sftp.connect({
        host: config.host,
        port: config.port ?? 22,
        username: config.username,
        password: config.password,
    });

    await sftp.put(join(root, file), `${remoteBase}/${file.replace(/\\/g, '/')}`);
    console.log(`OK  ${file}`);
} finally {
    await sftp.end();
}

console.log('--- Google verification file deployed ---');
