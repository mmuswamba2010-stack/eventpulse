import { readFileSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';
import { Client } from 'ssh2';

const __dirname = dirname(fileURLToPath(import.meta.url));
const config = JSON.parse(readFileSync(join(__dirname, '..', '.vscode', 'sftp.json'), 'utf8'));

const pattern = 'EVENTPULSE_ORGANIZER_MODERATION';
const sed = `grep -q '^${pattern}=' .env && sed -i 's|^${pattern}=.*|${pattern}=false|' .env || echo '${pattern}=false' >> .env`;

const command = [
    'cd ~/www',
    sed,
    `grep '^${pattern}=' .env`,
    'php artisan eventpulse:approve-pending-organizers',
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

console.log('Desactivation moderation organisateurs + approbation des comptes en attente…');
