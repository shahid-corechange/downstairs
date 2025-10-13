using Downstairs.Domain.Entities;
using Downstairs.Domain.Shared;
using Microsoft.EntityFrameworkCore;
using System.Reflection;

namespace Downstairs.Infrastructure.Persistence;

/// <summary>
/// Main database context for Downstairs application
/// </summary>
public class DownstairsDbContext : DbContext
{
    public DownstairsDbContext(DbContextOptions<DownstairsDbContext> options) : base(options)
    {
    }

    public DbSet<Customer> Customers => Set<Customer>();
    public DbSet<Invoice> Invoices => Set<Invoice>();
    public DbSet<InvoiceLine> InvoiceLines => Set<InvoiceLine>();

    protected override void OnModelCreating(ModelBuilder modelBuilder)
    {
        base.OnModelCreating(modelBuilder);

        // Apply configurations from current assembly
        modelBuilder.ApplyConfigurationsFromAssembly(Assembly.GetExecutingAssembly());

        // Configure domain events to be ignored by EF Core
        modelBuilder.Ignore<DomainEvent>();
    }

    public override async Task<int> SaveChangesAsync(CancellationToken cancellationToken = default)
    {
        // Handle domain events before saving changes
        await DispatchDomainEventsAsync();

        return await base.SaveChangesAsync(cancellationToken);
    }

    private Task DispatchDomainEventsAsync()
    {
        var domainEntities = ChangeTracker
            .Entries<Entity<Guid>>()
            .Where(x => x.Entity.DomainEvents.Count > 0)
            .ToList();

        var domainEvents = domainEntities
            .SelectMany(x => x.Entity.DomainEvents)
            .ToList();

        domainEntities.ForEach(entity => entity.Entity.ClearDomainEvents());

        // Here we would publish domain events via Dapr or MediatR
        // For now, just clear them to prevent persistence issues
        foreach (var domainEvent in domainEvents)
        {
            // TODO: Publish domain event via Dapr pub/sub
            // await daprClient.PublishEventAsync("pubsub", domainEvent.GetType().Name, domainEvent);
        }

        return Task.CompletedTask;
    }
}