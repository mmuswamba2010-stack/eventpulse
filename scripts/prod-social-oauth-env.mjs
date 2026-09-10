import { readFileSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';
import { Client } from 'ssh2';

const __dirname = dirname(fileURLToPath(import.meta.url));
const config = JSON.parse(readFileSync(join(__dirname, '..', '.vscode', 'sftp.json'), 'utf8'));

const appUrl = process.env.EVENTPULSE_APP_URL ?? 'https://eventpulse.alwaysdata.net';

const optional = {
    GOOGLE_CLIENT_ID: process.env.GOOGLE_CLIENT_ID,
    GOOGLE_CLIENT_SECRET: process.env.GOOGLE_CLIENT_SECRET,
    GOOGLE_REDIRECT_URI: process.env.GOOGLE_REDIRECT_URI ?? `${appUrl}/auth/google/callback`,
    FACEBOOK_CLIENT_ID: process.env.FACEBOOK_CLIENT_ID,
    FACEBOOK_CLIENT_SECRET: process.env.FACEBOOK_CLIENT_SECRET,
    FACEBOOK_REDIRECT_URI: process.env.FACEBOOK_REDIRECT_URI ?? `${appUrl}/auth/facebook/callback`,
};

const vars = Object.fromEntries(
    Object.entries(optional).filter(([, value]) => Boolean(value))
);

if (Object.keys(vars).length === 0) {
    console.error('Aucune variable OAuth fournie.');
    console.error('Exemple : $env:GOOGLE_CLIENT_ID="..."; $env:GOOGLE_CLIENT_SECRET="..."; node scripts/prod-social-oauth-env.mjs');
    process.exit(1);
}

const upsertLines = Object.entries(vars)
    .map(([key, value]) => {
        const escaped = String(value).replace(/'/g, `'\\''`);

        return `grep -q '^${key}=' .env && sed -i 's|^${key}=.*|${key}=${escaped}|' .env || echo '${key}=${escaped}' >> .env`;
    })
    .join(' && ');

const command = `cd ~/www && ${upsertLines} && php artisan config:clear && php artisan cache:clear && grep -E '^(GOOGLE_|FACEBOOK_)' .env`;

const conn = new Client();

conn.on('ready', () => {
    conn.exec(command, (err, stream) => {
        if (err) {
            console.error(err);
            conn.end();
            process.exit(1);
        }

        stream.on('close', (code) => {
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

console.log('Configuration OAuth prod en cours…');
