namespace Downstairs.Infrastructure.Persistence.Models;

public partial class FixedPrice
{
    public long Id { get; set; }

    public long UserId { get; set; }

    public string Type { get; set; } = null!;

    public DateOnly? StartDate { get; set; }

    public DateOnly? EndDate { get; set; }

    public bool IsPerOrder { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public DateTime? DeletedAt { get; set; }

    public virtual ICollection<FixedPriceRow> FixedPriceRows { get; set; } = new List<FixedPriceRow>();

    public virtual ICollection<OrderFixedPrice> OrderFixedPrices { get; set; } = new List<OrderFixedPrice>();

    public virtual ICollection<Subscription> Subscriptions { get; set; } = new List<Subscription>();

    public virtual User User { get; set; } = null!;

    public virtual ICollection<Product> Products { get; set; } = new List<Product>();
}