import { readFileSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';
import { Client } from 'ssh2';
import SftpClient from 'ssh2-sftp-client';

const __dirname = dirname(fileURLToPath(import.meta.url));
const root = join(__dirname, '..');
const config = JSON.parse(readFileSync(join(root, '.vscode', 'sftp.json'), 'utf8'));

const email = process.argv[2] ?? 'contact@eventpulse.cd';
const remoteBase = config.remotePath.replace(/\/$/, '');

const sftp = new SftpClient();
await sftp.connect({
    host: config.host,
    port: config.port ?? 22,
    username: config.username,
    password: config.password,
});
await sftp.mkdir(`${remoteBase}/app/Console/Commands`, true);
await sftp.put(
    join(root, 'app/Console/Commands/ResetPasswordCommand.php'),
    `${remoteBase}/app/Console/Commands/ResetPasswordCommand.php`,
);
await sftp.end();

const command = `cd ~/www && php artisan eventpulse:reset-password '${email.replace(/'/g, "'\\''")}' 2>&1`;

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
