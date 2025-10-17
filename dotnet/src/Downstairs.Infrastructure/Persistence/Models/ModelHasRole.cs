namespace Downstairs.Infrastructure.Persistence.Models;

public partial class ModelHasRole
{
    public long RoleId { get; set; }

    public string ModelType { get; set; } = null!;

    public long ModelId { get; set; }

    public virtual Role Role { get; set; } = null!;
}