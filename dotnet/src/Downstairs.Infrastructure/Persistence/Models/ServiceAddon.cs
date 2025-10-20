namespace Downstairs.Infrastructure.Persistence.Models;

public partial class ServiceAddon
{
    public long MyRowId { get; set; }

    public long ServiceId { get; set; }

    public long AddonId { get; set; }

    public virtual Addon Addon { get; set; } = null!;

    public virtual Service Service { get; set; } = null!;
}