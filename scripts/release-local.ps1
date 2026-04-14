Param(
    [string]$Version,
    [string]$Summary = "Platform release synced from application version.",
    [switch]$Force
)

$ErrorActionPreference = 'Stop'

Set-Location (Split-Path -Parent $PSScriptRoot)

if ([string]::IsNullOrWhiteSpace($Version)) {
    $Version = (git describe --tags --abbrev=0).Trim()
}

if ([string]::IsNullOrWhiteSpace($Version)) {
    Write-Error "No version found. Create a git tag first or pass -Version vX.Y.Z"
}

$forceArg = if ($Force.IsPresent) { '--force' } else { '' }

Write-Host "Syncing system update for version: $Version" -ForegroundColor Cyan

php artisan app:sync-system-updates-from-app-version --release-version=$Version --summary="$Summary" $forceArg

if ($LASTEXITCODE -ne 0) {
    Write-Error "Release sync failed for version: $Version"
}

Write-Host "Release sync completed for version: $Version" -ForegroundColor Green
