import { readFileSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';
import { Client } from 'ssh2';

const __dirname = dirname(fileURLToPath(import.meta.url));
const config = JSON.parse(readFileSync(join(join(__dirname, '..'), '.vscode', 'sftp.json'), 'utf8'));

const code = process.argv[2];

if (!code) {
    console.error('Usage: node scripts/prod-google-verification.mjs VOTRE_CODE_GOOGLE');
    process.exit(1);
}

const command = [
    'cd ~/www',
    `grep -q '^GOOGLE_SITE_VERIFICATION=' .env && sed -i 's|^GOOGLE_SITE_VERIFICATION=.*|GOOGLE_SITE_VERIFICATION=${code}|' .env || echo 'GOOGLE_SITE_VERIFICATION=${code}' >> .env`,
    'grep ^GOOGLE_SITE_VERIFICATION= .env',
    'php artisan config:clear',
    'php artisan view:clear',
].join(' && ');

const conn = new Client();

conn.on('ready', () => {
    conn.exec(command, (err, stream) => {
        if (err) {
            console.error(err);
            conn.end();
            return;
        }

        stream.on('close', (exitCode) => {
            console.log(`Exit code: ${exitCode ?? 0}`);
            conn.end();
            process.exit(exitCode ?? 0);
        });
        stream.on('data', (data) => process.stdout.write(data.toString()));
        stream.stderr.on('data', (data) => process.stderr.write(data.toString()));
    });
}).connect({
    host: config.host,
    port: config.port ?? 22,
    username: config.username,
    password: config.password,
});

conn.on('error', (err) => {
    console.error('SSH error:', err.message);
    process.exit(1);
});

console.log('Configuration Google Search Console en production…');
