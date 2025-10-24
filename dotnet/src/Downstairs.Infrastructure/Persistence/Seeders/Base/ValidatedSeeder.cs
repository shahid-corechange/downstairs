using Microsoft.EntityFrameworkCore;
using Microsoft.Extensions.Logging;

namespace Downstairs.Infrastructure.Persistence.Seeders.Base;

/// <summary>
/// Base class for seeders that require data validation
/// Provides validation methods for ensuring data integrity after seeding
/// </summary>
public abstract class ValidatedSeeder : BaseSeeder
{
    protected ValidatedSeeder(IServiceProvider serviceProvider) : base(serviceProvider)
    {
    }

    /// <summary>
    /// Validate seeded data using a custom validation function
    /// </summary>
    /// <typeparam name="T">Entity type</typeparam>
    /// <param name="dbSet">Database set to validate</param>
    /// <param name="validationFunc">Validation function to execute</param>
    /// <param name="entityName">Entity name for logging</param>
    /// <returns>True if validation passes</returns>
    protected async Task<bool> ValidateSeededDataAsync<T>(DbSet<T> dbSet, Func<IQueryable<T>, Task<bool>> validationFunc, string? entityName = null) where T : class
    {
        entityName ??= typeof(T).Name;

        try
        {
            Logger.LogInformation("Validating seeded data for {EntityName}", entityName);

            var isValid = await validationFunc(dbSet);

            if (isValid)
            {
                Logger.LogInformation("Data validation passed for {EntityName}", entityName);
            }
            else
            {
                Logger.LogWarning("Data validation failed for {EntityName}", entityName);
            }

            return isValid;
        }
        catch (Exception ex)
        {
            Logger.LogError(ex, "Error during data validation for {EntityName}", entityName);
            return false;
        }
    }

    /// <summary>
    /// Validate that minimum required records exist
    /// </summary>
    /// <typeparam name="T">Entity type</typeparam>
    /// <param name="dbSet">Database set to validate</param>
    /// <param name="minimumCount">Minimum number of records required</param>
    /// <param name="entityName">Entity name for logging</param>
    /// <returns>True if minimum count is met</returns>
    protected async Task<bool> ValidateMinimumCountAsync<T>(DbSet<T> dbSet, int minimumCount, string? entityName = null) where T : class
    {
        entityName ??= typeof(T).Name;

        var count = await dbSet.CountAsync();
        var isValid = count >= minimumCount;

        if (isValid)
        {
            Logger.LogInformation("Minimum count validation passed for {EntityName}: {Count}/{MinimumCount}",
                entityName, count, minimumCount);
        }
        else
        {
            Logger.LogWarning("Minimum count validation failed for {EntityName}: {Count}/{MinimumCount}",
                entityName, count, minimumCount);
        }

        return isValid;
    }

    /// <summary>
    /// Validate foreign key relationships
    /// </summary>
    /// <typeparam name="T">Entity type</typeparam>
    /// <param name="dbSet">Database set to validate</param>
    /// <param name="relationshipValidationFunc">Function to validate relationships</param>
    /// <param name="entityName">Entity name for logging</param>
    /// <returns>True if relationships are valid</returns>
    protected async Task<bool> ValidateRelationshipsAsync<T>(DbSet<T> dbSet, Func<IQueryable<T>, Task<bool>> relationshipValidationFunc, string? entityName = null) where T : class
    {
        entityName ??= typeof(T).Name;

        try
        {
            Logger.LogInformation("Validating relationships for {EntityName}", entityName);

            var isValid = await relationshipValidationFunc(dbSet);

            if (isValid)
            {
                Logger.LogInformation("Relationship validation passed for {EntityName}", entityName);
            }
            else
            {
                Logger.LogWarning("Relationship validation failed for {EntityName}", entityName);
            }

            return isValid;
        }
        catch (Exception ex)
        {
            Logger.LogError(ex, "Error during relationship validation for {EntityName}", entityName);
            return false;
        }
    }
}