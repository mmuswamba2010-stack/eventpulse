import { readFileSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';
import { Client } from 'ssh2';

const __dirname = dirname(fileURLToPath(import.meta.url));
const config = JSON.parse(readFileSync(join(__dirname, '..', '.vscode', 'sftp.json'), 'utf8'));

const vars = [
    ['EVENTPULSE_USD_ENABLED', 'true'],
    ['EVENTPULSE_CDF_PER_USD', '2250'],
];

const sedLines = vars.map(([key, value]) =>
    `grep -q '^${key}=' .env && sed -i 's|^${key}=.*|${key}=${value}|' .env || echo '${key}=${value}' >> .env`,
);

const command = [
    'cd ~/www',
    ...sedLines,
    'grep -E "^EVENTPULSE_(USD_ENABLED|CDF_PER_USD)=" .env',
    'php artisan config:clear',
    'php artisan config:cache',
].join(' && ');

const conn = new Client();

conn.on('ready', () => {
    conn.exec(command, (err, stream) => {
        if (err) {
            console.error(err);
            conn.end();
            return;
        }

        stream.on('close', (code) => {
            console.log(`Exit code: ${code}`);
            conn.end();
            process.exit(code ?? 0);
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

console.log('Configuration affichage USD en production…');
