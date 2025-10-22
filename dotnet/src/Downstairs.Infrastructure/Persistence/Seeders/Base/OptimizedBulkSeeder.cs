using Microsoft.EntityFrameworkCore;
using Microsoft.Extensions.Logging;

namespace Downstairs.Infrastructure.Persistence.Seeders.Base;

/// <summary>
/// Base class for seeders that need to handle large datasets efficiently
/// Provides bulk insert operations with batching and performance optimization
/// </summary>
public abstract class OptimizedBulkSeeder : BaseSeeder
{
    protected OptimizedBulkSeeder(IServiceProvider serviceProvider) : base(serviceProvider)
    {
    }

    /// <summary>
    /// Perform bulk insert with batching for large datasets
    /// </summary>
    /// <typeparam name="T">Entity type</typeparam>
    /// <param name="context">Database context</param>
    /// <param name="entities">Entities to insert</param>
    /// <param name="batchSize">Number of entities per batch</param>
    protected async Task BulkInsertAsync<T>(DownstairsDbContext context, IEnumerable<T> entities, int batchSize = 1000) where T : class
    {
        var entitiesList = entities.ToList();

        if (!entitiesList.Any())
        {
            Logger.LogInformation("No entities to insert for {EntityType}", typeof(T).Name);
            return;
        }

        Logger.LogInformation("Starting bulk insert of {Count} {EntityType} entities with batch size {BatchSize}",
            entitiesList.Count, typeof(T).Name, batchSize);

        // Disable change tracking for bulk operations
        context.ChangeTracker.AutoDetectChangesEnabled = false;

        try
        {
            var totalBatches = (entitiesList.Count + batchSize - 1) / batchSize;

            for (int i = 0; i < entitiesList.Count; i += batchSize)
            {
                var batch = entitiesList.Skip(i).Take(batchSize);
                await context.Set<T>().AddRangeAsync(batch);
                await context.SaveChangesAsync();

                // Clear change tracker to free memory
                context.ChangeTracker.Clear();

                var currentBatch = (i / batchSize) + 1;
                Logger.LogInformation("Processed batch {CurrentBatch}/{TotalBatches} for {EntityType}",
                    currentBatch, totalBatches, typeof(T).Name);
            }

            Logger.LogInformation("Completed bulk insert of {Count} {EntityType} entities",
                entitiesList.Count, typeof(T).Name);
        }
        finally
        {
            context.ChangeTracker.AutoDetectChangesEnabled = true;
        }
    }

    /// <summary>
    /// Execute raw SQL for very large datasets (like cities.sql)
    /// </summary>
    /// <param name="context">Database context</param>
    /// <param name="sqlContent">SQL content to execute</param>
    protected async Task ExecuteBulkSqlAsync(DownstairsDbContext context, string sqlContent)
    {
        if (string.IsNullOrWhiteSpace(sqlContent))
        {
            Logger.LogWarning("SQL content is empty, skipping execution");
            return;
        }

        Logger.LogInformation("Executing bulk SQL operation...");

        try
        {
            await context.Database.ExecuteSqlRawAsync(sqlContent);
            Logger.LogInformation("Completed bulk SQL operation");
        }
        catch (Exception ex)
        {
            Logger.LogError(ex, "Failed to execute bulk SQL operation");
            throw;
        }
    }
}