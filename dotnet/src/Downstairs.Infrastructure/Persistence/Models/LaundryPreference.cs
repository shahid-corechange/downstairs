namespace Downstairs.Infrastructure.Persistence.Models;

public partial class LaundryPreference
{
    public long Id { get; set; }

    public decimal Price { get; set; }

    public decimal Percentage { get; set; }

    public byte VatGroup { get; set; }

    public ushort Hours { get; set; }

    public bool IncludeHolidays { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public DateTime? DeletedAt { get; set; }

    public virtual ICollection<LaundryOrder> LaundryOrders { get; set; } = new List<LaundryOrder>();

    public virtual ICollection<SubscriptionLaundryDetail> SubscriptionLaundryDetails { get; set; } = new List<SubscriptionLaundryDetail>();
}