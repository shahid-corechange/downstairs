namespace Downstairs.Infrastructure.Persistence.Models;

public partial class Product
{
    public long Id { get; set; }

    public string? FortnoxArticleId { get; set; }

    public string? Unit { get; set; }

    public decimal Price { get; set; }

    public ushort? CreditPrice { get; set; }

    public byte VatGroup { get; set; }

    public bool HasRut { get; set; }

    public string? ThumbnailImage { get; set; }

    public string Color { get; set; } = null!;

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public DateTime? DeletedAt { get; set; }

    public virtual ICollection<LaundryOrderProduct> LaundryOrderProducts { get; set; } = new List<LaundryOrderProduct>();

    public virtual ICollection<Productable> Productables { get; set; } = new List<Productable>();

    public virtual ICollection<StoreProduct> StoreProducts { get; set; } = new List<StoreProduct>();

    public virtual ICollection<StoreSaleProduct> StoreSaleProducts { get; set; } = new List<StoreSaleProduct>();

    public virtual ICollection<FixedPrice> FixedPrices { get; set; } = new List<FixedPrice>();

    public virtual ICollection<OrderFixedPrice> OrderFixedPrices { get; set; } = new List<OrderFixedPrice>();
}