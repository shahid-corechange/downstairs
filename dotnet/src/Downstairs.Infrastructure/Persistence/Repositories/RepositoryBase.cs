using System.Linq.Expressions;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Task = System.Threading.Tasks.Task;

namespace Downstairs.Infrastructure.Persistence.Repositories;

/// <summary>
/// Generic repository that wraps a <see cref="DbSet{TEntity}"/> for scaffolded persistence models.
/// </summary>
/// <typeparam name="TEntity">The persistence model type.</typeparam>
internal class RepositoryBase<TEntity>
    where TEntity : class
{
    protected RepositoryBase(DownstairsDbContext context)
    {
        Context = context ?? throw new ArgumentNullException(nameof(context));
        Set = Context.Set<TEntity>();
    }

    protected DownstairsDbContext Context { get; }

    protected DbSet<TEntity> Set { get; }

    /// <summary>
    /// Returns an <see cref="IQueryable{TEntity}"/> for additional composition.
    /// </summary>
    public virtual IQueryable<TEntity> Query(bool asNoTracking = true)
    {
        return asNoTracking ? Set.AsNoTracking() : Set.AsQueryable();
    }

    /// <summary>
    /// Finds an entity by its primary key values.
    /// </summary>
    public virtual ValueTask<TEntity?> FindAsync(CancellationToken cancellationToken, params object?[] keyValues)
    {
        return Set.FindAsync(keyValues, cancellationToken);
    }

    /// <summary>
    /// Finds the first entity that matches the supplied predicate.
    /// </summary>
    public virtual Task<TEntity?> FirstOrDefaultAsync(Expression<Func<TEntity, bool>> predicate, CancellationToken cancellationToken = default)
    {
        return Query().FirstOrDefaultAsync(predicate, cancellationToken);
    }

    /// <summary>
    /// Determines whether any entity matches the supplied predicate.
    /// </summary>
    public virtual Task<bool> AnyAsync(Expression<Func<TEntity, bool>> predicate, CancellationToken cancellationToken = default)
    {
        return Query().AnyAsync(predicate, cancellationToken);
    }

    /// <summary>
    /// Materialises the current query into a list.
    /// </summary>
    public virtual Task<List<TEntity>> ToListAsync(CancellationToken cancellationToken = default)
    {
        return Query().ToListAsync(cancellationToken);
    }

    /// <summary>
    /// Adds a new entity to the set.
    /// </summary>
    public virtual Task AddAsync(TEntity entity, CancellationToken cancellationToken = default)
    {
        ArgumentNullException.ThrowIfNull(entity);
        return Set.AddAsync(entity, cancellationToken).AsTask();
    }

    /// <summary>
    /// Adds a range of entities to the set.
    /// </summary>
    public virtual Task AddRangeAsync(IEnumerable<TEntity> entities, CancellationToken cancellationToken = default)
    {
        ArgumentNullException.ThrowIfNull(entities);
        return Set.AddRangeAsync(entities, cancellationToken);
    }

    /// <summary>
    /// Marks an entity as modified.
    /// </summary>
    public virtual void Update(TEntity entity)
    {
        ArgumentNullException.ThrowIfNull(entity);
        Set.Update(entity);
    }

    /// <summary>
    /// Removes an entity from the set.
    /// </summary>
    public virtual void Remove(TEntity entity)
    {
        ArgumentNullException.ThrowIfNull(entity);
        Set.Remove(entity);
    }

    /// <summary>
    /// Removes multiple entities from the set.
    /// </summary>
    public virtual void RemoveRange(IEnumerable<TEntity> entities)
    {
        ArgumentNullException.ThrowIfNull(entities);
        Set.RemoveRange(entities);
    }
}

