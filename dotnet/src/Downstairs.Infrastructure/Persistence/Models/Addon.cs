namespace Downstairs.Infrastructure.Persistence.Models;

public partial class Addon
{
    public long Id { get; set; }

    public string? FortnoxArticleId { get; set; }

    public string? Unit { get; set; }

    public decimal Price { get; set; }

    public ushort CreditPrice { get; set; }

    public byte VatGroup { get; set; }

    public bool HasRut { get; set; }

    public string? ThumbnailImage { get; set; }

    public string Color { get; set; } = null!;

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public DateTime? DeletedAt { get; set; }

    public virtual ICollection<ServiceAddon> ServiceAddons { get; set; } = new List<ServiceAddon>();
}