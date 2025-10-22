namespace Downstairs.Infrastructure.Persistence.Seeders.Base;

/// <summary>
/// Base class for seeders that require transactional operations
/// Provides transaction management with rollback capability
/// </summary>
public abstract class TransactionalSeeder : BaseSeeder
{
    protected TransactionalSeeder(IServiceProvider serviceProvider) : base(serviceProvider)
    {
    }

    /// <summary>
    /// Execute seeding operation within a transaction
    /// </summary>
    /// <param name="context">Database context</param>
    /// <param name="seederAction">Seeding action to execute</param>
    protected async Task ExecuteWithTransactionAsync(DownstairsDbContext context, Func<Task> seederAction)
    {
        using var transaction = await context.Database.BeginTransactionAsync();

        try
        {
            Logger.LogInformation("Starting transactional seeding: {SeederName}", Name);

            await seederAction();
            await context.SaveChangesAsync();
            await transaction.CommitAsync();

            Logger.LogInformation("Successfully completed transactional seeding: {SeederName}", Name);
        }
        catch (Exception ex)
        {
            await transaction.RollbackAsync();
            Logger.LogError(ex, "Transactional seeding failed, rolled back: {SeederName}", Name);
            throw;
        }
    }

    /// <summary>
    /// Execute multiple related seeding operations in a single transaction
    /// </summary>
    /// <param name="context">Database context</param>
    /// <param name="seederActions">Multiple seeding actions to execute</param>
    protected async Task ExecuteMultipleWithTransactionAsync(DownstairsDbContext context, params Func<Task>[] seederActions)
    {
        using var transaction = await context.Database.BeginTransactionAsync();

        try
        {
            Logger.LogInformation("Starting multi-operation transactional seeding: {SeederName}", Name);

            foreach (var action in seederActions)
            {
                await action();
            }

            await context.SaveChangesAsync();
            await transaction.CommitAsync();

            Logger.LogInformation("Successfully completed multi-operation transactional seeding: {SeederName}", Name);
        }
        catch (Exception ex)
        {
            await transaction.RollbackAsync();
            Logger.LogError(ex, "Multi-operation transactional seeding failed, rolled back: {SeederName}", Name);
            throw;
        }
    }
}