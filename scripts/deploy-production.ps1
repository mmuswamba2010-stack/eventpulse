# Deploie les fichiers des fonctionnalites (langue, +243, theme) vers AlwaysData.
# Usage: powershell -ExecutionPolicy Bypass -File scripts/deploy-production.ps1

$ErrorActionPreference = 'Stop'

if (-not (Get-Module -ListAvailable -Name Posh-SSH)) {
    Write-Host 'Installation de Posh-SSH...'
    Install-Module -Name Posh-SSH -Scope CurrentUser -Force -AllowClobber
}

Import-Module Posh-SSH

$sftpConfig = Get-Content (Join-Path $PSScriptRoot '..\.vscode\sftp.json') -Raw | ConvertFrom-Json
$base = Resolve-Path (Join-Path $PSScriptRoot '..')
$remoteBase = $sftpConfig.remotePath.TrimEnd('/')

$files = @(
    'lang/fr.json',
    'lang/en.json',
    'app/Http/Middleware/SetLocale.php',
    'app/Http/Controllers/LocaleController.php',
    'bootstrap/app.php',
    'routes/web.php',
    'config/eventpulse.php',
    'resources/views/partials/theme-init.blade.php',
    'resources/views/components/locale-theme-toggle.blade.php',
    'resources/views/layouts/app.blade.php',
    'resources/views/layouts/guest.blade.php',
    'resources/views/layouts/navigation.blade.php',
    'resources/views/auth/login.blade.php',
    'resources/views/auth/register.blade.php',
    'resources/views/events/index.blade.php',
    'resources/views/events/show.blade.php',
    'resources/views/organizer/events/_form.blade.php',
    'resources/views/organizer/events/pay.blade.php',
    'resources/views/components/nav-link.blade.php',
    'resources/views/components/responsive-nav-link.blade.php',
    'resources/views/components/event-card.blade.php',
    'resources/views/components/category-tile.blade.php',
    'resources/views/components/brand-logo.blade.php',
    'public/build/manifest.json',
    'public/build/assets/app-Cj81_60J.css',
    'public/build/assets/app-mYZXg42s.js'
)

$secure = ConvertTo-SecureString $sftpConfig.password -AsPlainText -Force
$cred = New-Object System.Management.Automation.PSCredential ($sftpConfig.username, $secure)

Write-Host "Connexion SFTP a $($sftpConfig.host)..."
$session = New-SFTPSession -ComputerName $sftpConfig.host -Port $sftpConfig.port -Credential $cred -AcceptKey

if (-not $session) {
    throw 'Connexion SFTP echouee.'
}

$ok = 0
$fail = 0

foreach ($rel in $files) {
    $local = Join-Path $base $rel
    $remote = "$remoteBase/$($rel -replace '\\', '/')"
    $remoteDir = ($remote -replace '[^/]+$', '').TrimEnd('/')

    try {
        $null = Invoke-SSHCommand -SessionId $session.SessionId -Command "mkdir -p `"$remoteDir`"" -TimeOut 30
        Set-SFTPItem -SessionId $session.SessionId -Path $local -Destination $remote -Force
        Write-Host "OK  $rel"
        $ok++
    } catch {
        Write-Host "FAIL $rel - $($_.Exception.Message)"
        $fail++
    }
}

Remove-SFTPSession -SessionId $session.SessionId | Out-Null
Write-Host "--- Termine: $ok ok, $fail echecs ---"

if ($fail -gt 0) { exit 1 }
