#!/usr/bin/env pwsh
# Downstairs Container Management Script
# This script helps manage the Downstairs container stack

param(
    [Parameter(Mandatory=$true)]
    [ValidateSet("start", "stop", "restart", "status", "logs", "clean", "network")]
    [string]$Command,
    
    [Parameter(Mandatory=$false)]
    [string]$Service = "all"
)

$NetworkName = "downstairs-local"
$ProjectName = "downstairs"

function Write-Header {
    param([string]$Message)
    Write-Host "`n🚀 $Message" -ForegroundColor Cyan
    Write-Host ("=" * ($Message.Length + 3)) -ForegroundColor Cyan
}

function Write-Success {
    param([string]$Message)
    Write-Host "✅ $Message" -ForegroundColor Green
}

function Write-Error {
    param([string]$Message)
    Write-Host "❌ $Message" -ForegroundColor Red
}

function Write-Info {
    param([string]$Message)
    Write-Host "ℹ️  $Message" -ForegroundColor Yellow
}

switch ($Command) {
    "start" {
        Write-Header "Starting Downstairs Container Stack"
        
        # Create network if it doesn't exist
        $networkExists = docker network ls --format "{{.Name}}" | Where-Object { $_ -eq $NetworkName }
        if (-not $networkExists) {
            Write-Info "Creating Docker network: $NetworkName"
            docker network create $NetworkName --subnet=172.20.0.0/16
        } else {
            Write-Info "Network $NetworkName already exists"
        }
        
        # Start infrastructure services
        Write-Info "Starting infrastructure containers..."
        docker-compose up -d
        
        # Wait for services to be healthy
        Write-Info "Waiting for services to become healthy..."
        Start-Sleep -Seconds 10
        
        # Start Aspire application stack
        Write-Info "Starting Aspire application stack..."
        Write-Host "Run the following command in another terminal:" -ForegroundColor Magenta
        Write-Host "dotnet run --project Downstairs.AppHost" -ForegroundColor White
        
        Write-Success "Infrastructure started! Check status with: .\manage-containers.ps1 status"
    }
    
    "stop" {
        Write-Header "Stopping Downstairs Container Stack"
        
        Write-Info "Stopping Aspire services (if running)..."
        # Aspire services will stop when the AppHost is terminated
        
        Write-Info "Stopping infrastructure containers..."
        docker-compose down
        
        Write-Success "Container stack stopped"
    }
    
    "restart" {
        Write-Header "Restarting Downstairs Container Stack"
        & $PSCommandPath -Command "stop" -Service $Service
        Start-Sleep -Seconds 5
        & $PSCommandPath -Command "start" -Service $Service
    }
    
    "status" {
        Write-Header "Downstairs Container Status"
        
        Write-Info "Network Status:"
        docker network ls | Select-String $NetworkName
        
        Write-Info "`nInfrastructure Containers:"
        docker ps --filter "name=downstairs-*" --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"
        
        Write-Info "`nContainer Health:"
        $containers = @("downstairs-mysql", "downstairs-redis")
        foreach ($container in $containers) {
            $health = docker inspect $container --format='{{.State.Health.Status}}' 2>$null
            if ($health) {
                $status = if ($health -eq "healthy") { "🟢" } else { "🔴" }
                Write-Host "$status $container`: $health"
            } else {
                $running = docker ps --filter "name=$container" --format "{{.Names}}" 2>$null
                if ($running) {
                    Write-Host "🟡 $container`: running (no health check)"
                } else {
                    Write-Host "🔴 $container`: not running"
                }
            }
        }
        
        Write-Info "`nVolume Usage:"
        docker volume ls --filter "name=downstairs-*" --format "table {{.Name}}\t{{.Driver}}"
    }
    
    "logs" {
        Write-Header "Container Logs"
        
        if ($Service -eq "all") {
            Write-Info "Showing logs for all infrastructure containers..."
            docker-compose logs -f --tail=50
        } else {
            Write-Info "Showing logs for: downstairs-$Service"
            docker logs -f --tail=50 "downstairs-$Service"
        }
    }
    
    "clean" {
        Write-Header "Cleaning Downstairs Resources"
        
        Write-Warning "This will remove all containers, volumes, and networks!"
        $confirm = Read-Host "Are you sure? (y/N)"
        
        if ($confirm -eq "y" -or $confirm -eq "Y") {
            Write-Info "Stopping and removing containers..."
            docker-compose down -v --remove-orphans
            
            Write-Info "Removing network..."
            docker network rm $NetworkName 2>$null
            
            Write-Info "Removing volumes..."
            docker volume rm downstairs-mysql-data downstairs-redis-data 2>$null
            
            Write-Success "Cleanup completed"
        } else {
            Write-Info "Cleanup cancelled"
        }
    }
    
    "network" {
        Write-Header "Network Information"
        
        Write-Info "Network Details:"
        docker network inspect $NetworkName 2>/dev/null || Write-Error "Network $NetworkName not found"
        
        Write-Info "`nConnected Containers:"
        docker network inspect $NetworkName --format='{{range .Containers}}{{.Name}} ({{.IPv4Address}}){{println}}{{end}}' 2>/dev/null
    }
}

Write-Host "" # Add blank line at end