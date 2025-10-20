namespace Downstairs.Infrastructure.Persistence.Models;

public partial class Category
{
    public long Id { get; set; }

    public string? ThumbnailImage { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public DateTime? DeletedAt { get; set; }

    public virtual ICollection<Categoryable> Categoryables { get; set; } = new List<Categoryable>();
}