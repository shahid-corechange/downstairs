# PowerShell script to remove specific lines from MySQL dump files

Write-Host "Starting to remove specific lines from MySQL dump files..." -ForegroundColor Green

# Define the patterns to remove (entire lines)
$linePatterns = @(
    "-- Dump completed on"
)

# Define the text patterns to replace within lines
$textReplacements = @{
}

# Define regex patterns to replace within lines
$regexReplacements = @{
}

# Get all SQL files in both directories
$sqlFiles = @()
$sqlFiles += Get-ChildItem -Path "Dump_Prod_Structure" -Filter "*.sql" -File
$sqlFiles += Get-ChildItem -Path "Dump_Stage_Structure" -Filter "*.sql" -File
$sqlFiles += Get-ChildItem -Path "Dump_Local_Structure" -Filter "*.sql" -File

Write-Host "Found $($sqlFiles.Count) SQL files to process" -ForegroundColor Yellow

$processedFiles = 0
$totalLinesRemoved = 0

foreach ($file in $sqlFiles) {
    Write-Host "Processing: $($file.Name)" -ForegroundColor Cyan
    
    # Read all lines from the file
    $lines = Get-Content -Path $file.FullName
    $originalLineCount = $lines.Count
    $linesRemoved = 0
    
    # Filter out lines that match patterns and replace text within lines
    $filteredLines = @()
    foreach ($line in $lines) {
        $shouldRemove = $false
        $modifiedLine = $line
        
        # Check if entire line should be removed
        foreach ($pattern in $linePatterns) {
            if ($line -match $pattern) {
                $shouldRemove = $true
                $linesRemoved++
                Write-Host "   Removing line: $line" -ForegroundColor Red
                break
            }
        }
        
        # If line is not being removed, check for text replacements
        if (-not $shouldRemove) {
            $originalLine = $modifiedLine
            
            # Handle literal text replacements
            foreach ($find in $textReplacements.Keys) {
                $replace = $textReplacements[$find]
                if ($modifiedLine -match [regex]::Escape($find)) {
                    $modifiedLine = $modifiedLine -replace [regex]::Escape($find), $replace
                    Write-Host "   Replacing text in: $originalLine" -ForegroundColor Yellow
                    Write-Host "   Modified to: $modifiedLine" -ForegroundColor Green
                    $linesRemoved++  # Count text replacements as modifications
                }
            }
            
            # Handle regex replacements
            foreach ($find in $regexReplacements.Keys) {
                $replace = $regexReplacements[$find]
                if ($modifiedLine -match $find) {
                    $modifiedLine = $modifiedLine -replace $find, $replace
                    Write-Host "   Replacing regex pattern in: $originalLine" -ForegroundColor Yellow
                    Write-Host "   Modified to: $modifiedLine" -ForegroundColor Green
                    $linesRemoved++  # Count text replacements as modifications
                }
            }
            
            $filteredLines += $modifiedLine
        }
    }
    
    # Write the filtered content back to the file
    if ($linesRemoved -gt 0) {
        $filteredLines | Set-Content -Path $file.FullName -Encoding UTF8
        Write-Host "   Modified $linesRemoved items in $($file.Name)" -ForegroundColor Green
        $totalLinesRemoved += $linesRemoved
    } else {
        Write-Host "   No matching patterns found in $($file.Name)" -ForegroundColor Gray
    }
    
    $processedFiles++
}

Write-Host ""
Write-Host "Processing complete!" -ForegroundColor Green
Write-Host "Files processed: $processedFiles" -ForegroundColor Yellow
Write-Host "Total lines removed/modified: $totalLinesRemoved" -ForegroundColor Yellow