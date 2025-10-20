namespace Downstairs.Infrastructure.Persistence.Models;

public partial class Invoice
{
    public long Id { get; set; }

    public long UserId { get; set; }

    public long CustomerId { get; set; }

    public long? FortnoxInvoiceId { get; set; }

    public long? FortnoxTaxReductionId { get; set; }

    public string? Type { get; set; }

    public string Category { get; set; } = null!;

    public int Month { get; set; }

    public int Year { get; set; }

    public string? Remark { get; set; }

    public decimal TotalGross { get; set; }

    public decimal TotalNet { get; set; }

    public decimal TotalVat { get; set; }

    public decimal TotalRut { get; set; }

    public string Status { get; set; } = null!;

    public DateTime? SentAt { get; set; }

    public DateTime? DueAt { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public DateTime? DeletedAt { get; set; }

    public virtual Customer Customer { get; set; } = null!;

    public virtual ICollection<Order> Orders { get; set; } = new List<Order>();

    public virtual User User { get; set; } = null!;
}