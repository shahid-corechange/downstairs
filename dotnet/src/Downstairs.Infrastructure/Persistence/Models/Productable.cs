namespace Downstairs.Infrastructure.Persistence.Models;

public partial class Productable
{
    public long Id { get; set; }

    public long ProductId { get; set; }

    public string ProductableType { get; set; } = null!;

    public long ProductableId { get; set; }

    public virtual Product Product { get; set; } = null!;
}