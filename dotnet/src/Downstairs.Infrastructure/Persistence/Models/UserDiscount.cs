namespace Downstairs.Infrastructure.Persistence.Models;

public partial class UserDiscount
{
    public long Id { get; set; }

    public int UserId { get; set; }

    public string Type { get; set; } = null!;

    public string Status { get; set; } = null!;

    public int? ProductId { get; set; }

    public int? ProductGroup { get; set; }

    public DateTime? ValidFromAt { get; set; }

    public DateTime? ValidToAt { get; set; }

    public int Repeatable { get; set; }

    public int Discount { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public DateTime? DeletedAt { get; set; }
}