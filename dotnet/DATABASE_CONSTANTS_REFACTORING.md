# Database Constants Refactoring Summary

## Overview
Moved hardcoded collation and charset values throughout the solution to centralized constants to improve maintainability and consistency.

## Changes Made

### 1. Created Constants
- **Location**: `Downstairs.ServiceDefaults\Constants\DatabaseConstants.cs`
- **Purpose**: Central location for all database-related constants
- **Constants Added**:
  - `DatabaseConstants.Collations.Unicode` = "utf8mb4_unicode_ci"
  - `DatabaseConstants.Collations.Swedish` = "utf8mb4_swedish_ci"
  - `DatabaseConstants.Collations.Utf8mb3General` = "utf8mb3_general_ci"
  - `DatabaseConstants.CharSets.Utf8mb4` = "utf8mb4"
  - `DatabaseConstants.CharSets.Utf8mb3` = "utf8mb3"

### 2. Infrastructure Layer Alias
- **Location**: `Downstairs.Infrastructure\Persistence\Constants\DatabaseConstants.cs`
- **Purpose**: Provides aliases to ServiceDefaults constants for Infrastructure layer usage
- **Benefit**: Maintains existing import structure while using centralized constants

### 3. Updated Files

#### Entity Framework Configurations (79 files updated)
All configuration files in `src\Downstairs.Infrastructure\Persistence\Configurations\`:
- Replaced `"utf8mb4_unicode_ci"` → `DatabaseConstants.Collations.Unicode`
- Replaced `"utf8mb4_swedish_ci"` → `DatabaseConstants.Collations.Swedish` (TeamConfiguration.cs)
- Replaced `"utf8mb3"` → `DatabaseConstants.CharSets.Utf8mb3` (OrderFixedPrice files)
- Replaced `"utf8mb3_general_ci"` → `DatabaseConstants.Collations.Utf8mb3General`
- Added `using Downstairs.Infrastructure.Persistence.Constants;` to all configuration files

#### DbContext
- **File**: `DownstairsDbContext.cs`
- **Changes**: Updated model builder to use constants instead of hardcoded strings

#### Application Host
- **File**: `Downstairs.AppHost\AppHost.cs`
- **Changes**: Updated MySQL container arguments to use constants for charset and collation

#### Docker Compose
- **File**: `docker-compose.yml`
- **Changes**: Added comment referencing the constants location for maintenance

## Benefits
1. **Maintainability**: Single point of change for database constants
2. **Consistency**: Ensures all parts of the application use the same values
3. **Type Safety**: Compile-time checking of constant usage
4. **Documentation**: Clear documentation of what each constant represents
5. **Refactoring Safety**: IntelliSense and refactoring tools can track usage

## Usage
```csharp
// In Entity Framework configurations
entity.UseCollation(DatabaseConstants.Collations.Unicode);
entity.HasCharSet(DatabaseConstants.CharSets.Utf8mb4);

// In AppHost for container configuration
.WithArgs($"--character-set-server={DatabaseConstants.CharSets.Utf8mb4}", 
          $"--collation-server={DatabaseConstants.Collations.Unicode}");
```

## Files Affected
- 1 new constants file in ServiceDefaults
- 1 new alias constants file in Infrastructure
- 79+ Entity Framework configuration files
- 1 DbContext file
- 1 AppHost file
- 1 Docker Compose file (documentation only)

All changes maintain backward compatibility and existing functionality while providing better maintainability for future development.