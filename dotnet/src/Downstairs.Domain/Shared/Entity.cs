namespace Downstairs.Domain.Shared;

/// <summary>
/// Base class for domain entities with identity
/// </summary>
public abstract class Entity<TId> : IEquatable<Entity<TId>>
{
    public TId Id { get; protected set; } = default!;
    
    private readonly List<DomainEvent> _domainEvents = [];
    
    public IReadOnlyCollection<DomainEvent> DomainEvents => _domainEvents.AsReadOnly();

    protected Entity() { }
    
    protected Entity(TId id)
    {
        Id = id;
    }

    public void AddDomainEvent(DomainEvent eventItem)
    {
        _domainEvents.Add(eventItem);
    }

    public void RemoveDomainEvent(DomainEvent eventItem)
    {
        _domainEvents.Remove(eventItem);
    }

    public void ClearDomainEvents()
    {
        _domainEvents.Clear();
    }

    public bool Equals(Entity<TId>? other)
    {
        return other is not null && Id!.Equals(other.Id);
    }

    public override bool Equals(object? obj)
    {
        return obj is Entity<TId> entity && Equals(entity);
    }

    public override int GetHashCode()
    {
        return Id!.GetHashCode();
    }

    public static bool operator ==(Entity<TId>? left, Entity<TId>? right)
    {
        return Equals(left, right);
    }

    public static bool operator !=(Entity<TId>? left, Entity<TId>? right)
    {
        return !Equals(left, right);
    }
}