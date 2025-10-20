namespace Downstairs.Infrastructure.Persistence.Models;

public partial class Store
{
    public long Id { get; set; }

    public long AddressId { get; set; }

    public string Name { get; set; } = null!;

    public string CompanyNumber { get; set; } = null!;

    public string? Phone { get; set; }

    public string? DialCode { get; set; }

    public string? Email { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public DateTime? DeletedAt { get; set; }

    public virtual Address Address { get; set; } = null!;

    public virtual ICollection<CashierAttendance> CashierAttendances { get; set; } = new List<CashierAttendance>();

    public virtual ICollection<LaundryOrder> LaundryOrders { get; set; } = new List<LaundryOrder>();

    public virtual ICollection<StoreProduct> StoreProducts { get; set; } = new List<StoreProduct>();

    public virtual ICollection<StoreSale> StoreSales { get; set; } = new List<StoreSale>();

    public virtual ICollection<StoreUser> StoreUsers { get; set; } = new List<StoreUser>();

    public virtual ICollection<SubscriptionLaundryDetail> SubscriptionLaundryDetails { get; set; } = new List<SubscriptionLaundryDetail>();
}