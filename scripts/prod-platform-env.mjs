import { readFileSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';
import { Client } from 'ssh2';
import { randomBytes } from 'node:crypto';

const __dirname = dirname(fileURLToPath(import.meta.url));
const config = JSON.parse(readFileSync(join(__dirname, '..', '.vscode', 'sftp.json'), 'utf8'));

const secret = randomBytes(32).toString('hex');
const pattern = 'EVENTPULSE_WEBHOOK_SECRET';
const sed = `grep -q '^${pattern}=' .env && sed -i 's|^${pattern}=.*|${pattern}=${secret}|' .env || echo '${pattern}=${secret}' >> .env`;
const moderation = `grep -q '^EVENTPULSE_ORGANIZER_MODERATION=' .env && sed -i 's|^EVENTPULSE_ORGANIZER_MODERATION=.*|EVENTPULSE_ORGANIZER_MODERATION=true|' .env || echo 'EVENTPULSE_ORGANIZER_MODERATION=true' >> .env`;

const command = `cd ~/www && ${sed} && ${moderation} && grep -E '^EVENTPULSE_(WEBHOOK_SECRET|ORGANIZER_MODERATION)=' .env`;

const conn = new Client();

conn.on('ready', () => {
    conn.exec(command, (err, stream) => {
        if (err) {
            console.error(err);
            conn.end();
            return;
        }

        stream.on('close', () => conn.end());
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

console.log('Webhook secret genere et ecrit dans ~/www/.env (prod)');
