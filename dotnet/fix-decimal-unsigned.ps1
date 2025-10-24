#!/usr/bin/env pwsh
# Script to fix decimal(8,2) to decimal(8,2) unsigned in migration files
# This is needed because EF Core doesn't generate the unsigned keyword correctly

param(
    [Parameter(Mandatory=$true)]
    [string]$MigrationFile
)

if (-not (Test-Path $MigrationFile)) {
    Write-Error "Migration file not found: $MigrationFile"
    exit 1
}

Write-Host "Fixing decimal unsigned in migration: $MigrationFile"

# Read the file content
$content = Get-Content $MigrationFile -Raw

# Define the columns that should be unsigned decimal(8,2)
$unsignedColumns = @(
    'price',
    'quantity', 
    'discount',
    'previous_price',
    'square_meter',
    'fixed_price',
    'percentage'
)

# Replace decimal(8,2) with decimal(8,2) unsigned for specific columns
foreach ($column in $unsignedColumns) {
    # Pattern to match the column definition
    $pattern = "($column\s*=\s*table\.Column<decimal>\(type:\s*`"decimal\(8,2\)`")"
    $replacement = "`$1 unsigned"
    $content = $content -replace $pattern, $replacement
}

# Also fix direct decimal(8,2) references
$content = $content -replace 'type:\s*"decimal\(8,2\)"(?!\s+unsigned)', 'type: "decimal(8,2) unsigned"'

# Write the updated content back
Set-Content $MigrationFile -Value $content -NoNewline

Write-Host "✅ Fixed decimal unsigned in migration file"
Write-Host "⚠️  Please review the changes before applying the migration"