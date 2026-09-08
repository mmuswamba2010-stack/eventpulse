import { readFileSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';
import SftpClient from 'ssh2-sftp-client';
import { Client } from 'ssh2';

const __dirname = dirname(fileURLToPath(import.meta.url));
const root = join(__dirname, '..');
const config = JSON.parse(readFileSync(join(root, '.vscode', 'sftp.json'), 'utf8'));

const files = [
    'app/Http/Controllers/Admin/PaymentController.php',
    'app/Http/Controllers/SitemapController.php',
    'app/Http/Controllers/RobotsController.php',
    'routes/web.php',
    'resources/views/sitemap.blade.php',
    'resources/views/admin/_nav.blade.php',
    'resources/views/admin/payments/index.blade.php',
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

const clearCommand = 'cd ~/www && php artisan route:clear && php artisan config:clear && php artisan view:clear && php artisan cache:clear && curl -sI https://eventpulse.alwaysdata.net/sitemap.xml | head -3';

await new Promise((resolve, reject) => {
    const conn = new Client();
    conn.on('ready', () => {
        conn.exec(clearCommand, (err, stream) => {
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

console.log('--- Fix sitemap prod: done ---');
