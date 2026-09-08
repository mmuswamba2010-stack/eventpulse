import { readFileSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';
import SftpClient from 'ssh2-sftp-client';

const __dirname = dirname(fileURLToPath(import.meta.url));
const root = join(__dirname, '..');
const config = JSON.parse(readFileSync(join(root, '.vscode', 'sftp.json'), 'utf8'));

const files = [
    'config/eventpulse.php',
    'app/Support/Seo.php',
    'app/Http/Controllers/SitemapController.php',
    'app/Http/Controllers/RobotsController.php',
    'app/Http/Controllers/EventController.php',
    'app/View/Components/AppLayout.php',
    'routes/web.php',
    'resources/views/partials/seo-meta.blade.php',
    'resources/views/sitemap.blade.php',
    'resources/views/layouts/app.blade.php',
    'resources/views/layouts/guest.blade.php',
    'resources/views/events/index.blade.php',
    'resources/views/events/show.blade.php',
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
        await sftp.put(join(root, rel), `${remoteBase}/${rel.replace(/\\/g, '/')}`);
        console.log(`OK  ${rel}`);
    }

    try {
        await sftp.delete(`${remoteBase}/public/robots.txt`);
        console.log('DEL public/robots.txt (remplacé par route dynamique)');
    } catch {
        console.log('SKIP public/robots.txt (absent)');
    }
} finally {
    await sftp.end();
}

console.log('--- Deploy SEO: done ---');
