import { readFileSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';
import { Client } from 'ssh2';

const __dirname = dirname(fileURLToPath(import.meta.url));
const config = JSON.parse(readFileSync(join(__dirname, '..', '.vscode', 'sftp.json'), 'utf8'));

const updates = [
    ['MAIL_MAILER', 'sendmail'],
    ['MAIL_SENDMAIL_PATH', '"/usr/sbin/sendmail -t -i"'],
    ['MAIL_FROM_ADDRESS', '"eventpulse@alwaysdata.net"'],
    ['MAIL_FROM_NAME', '"Event Pulse"'],
];

const sedCommands = updates
    .map(([key, value]) => {
        const pattern = key.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        return `grep -q '^${pattern}=' .env && sed -i 's|^${pattern}=.*|${pattern}=${value}|' .env || echo '${pattern}=${value}' >> .env`;
    })
    .join(' && ');

const command = `cd ~/www && ${sedCommands} && php artisan config:clear && php artisan cache:clear`;

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
