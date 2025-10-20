namespace Downstairs.Infrastructure.Persistence.Models;

public partial class Categoryable
{
    public long Id { get; set; }

    public long CategoryId { get; set; }

    public string CategoryableType { get; set; } = null!;

    public long CategoryableId { get; set; }

    public virtual Category Category { get; set; } = null!;
}