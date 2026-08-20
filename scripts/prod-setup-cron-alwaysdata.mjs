import { readFileSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const config = JSON.parse(readFileSync(join(__dirname, '..', '.vscode', 'sftp.json'), 'utf8'));

const apiKey = process.env.ALWAYSDATA_API_KEY?.trim();

if (! apiKey) {
    console.error('Manquant: ALWAYSDATA_API_KEY (Profil > Tokens sur admin.alwaysdata.com)');
    console.error('Exemple PowerShell:');
    console.error('  $env:ALWAYSDATA_API_KEY="votre_token"; node scripts/prod-setup-cron-alwaysdata.mjs');
    process.exit(1);
}

const account = config.username;
const authUser = `${apiKey} account=${account}:`;
const auth = Buffer.from(authUser).toString('base64');
const annotation = 'Event Pulse schedule:run';

async function api(path, options = {}) {
    const response = await fetch(`https://api.alwaysdata.com/v1${path}`, {
        ...options,
        headers: {
            Authorization: `Basic ${auth}`,
            Accept: 'application/json',
            ...(options.body ? { 'Content-Type': 'application/json' } : {}),
            ...options.headers,
        },
    });

    const text = await response.text();
    let data = null;
    try {
        data = text ? JSON.parse(text) : null;
    } catch {
        data = text;
    }

    if (! response.ok) {
        throw new Error(`HTTP ${response.status} ${path}: ${typeof data === 'string' ? data : JSON.stringify(data)}`);
    }

    return data;
}

function normalizeJobs(payload) {
    if (Array.isArray(payload)) {
        return payload;
    }

    if (payload?.items && Array.isArray(payload.items)) {
        return payload.items;
    }

    return [];
}

async function main() {
    const jobs = normalizeJobs(await api('/job/'));
    const existing = jobs.find((job) => job.annotation === annotation);

    const payload = {
        type: 'TYPE_COMMAND',
        argument: 'php artisan schedule:run',
        working_directory: 'www',
        date_type: 'FREQUENCY',
        frequency: 1,
        frequency_period: 'minute',
        annotation,
        is_disabled: false,
    };

    if (existing?.href) {
        const updated = await api(existing.href.replace('https://api.alwaysdata.com/v1', ''), {
            method: 'PUT',
            body: JSON.stringify(payload),
        });
        console.log('Tache planifiee mise a jour:', updated.id ?? existing.id);
    } else {
        const created = await api('/job/', {
            method: 'POST',
            body: JSON.stringify(payload),
        });
        console.log('Tache planifiee creee:', created.id ?? created.href);
    }

    console.log('Commande: php artisan schedule:run — toutes les minutes');
    console.log('Compte:', account);
}

main().catch((error) => {
    console.error('Echec:', error.message);
    process.exit(1);
});
