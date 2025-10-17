namespace Downstairs.Infrastructure.Persistence.Models;

public partial class Product
{
    public long Id { get; set; }

    public long? ServiceId { get; set; }

    public string? FortnoxArticleId { get; set; }

    public long? CategoryId { get; set; }

    public string? Unit { get; set; }

    public decimal Price { get; set; }

    public ushort? CreditPrice { get; set; }

    public byte VatGroup { get; set; }

    public bool HasRut { get; set; }

    public bool InApp { get; set; }

    public bool InStore { get; set; }

    public string? ThumbnailImage { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public DateTime? DeletedAt { get; set; }

    public virtual ProductCategory? Category { get; set; }

    public virtual ICollection<ScheduleCleaningProduct> ScheduleCleaningProducts { get; set; } = new List<ScheduleCleaningProduct>();

    public virtual Service? Service { get; set; }

    public virtual ICollection<SubscriptionProduct> SubscriptionProducts { get; set; } = new List<SubscriptionProduct>();
}