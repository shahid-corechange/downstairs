using System;
using System.Collections.Generic;

namespace Downstairs.Infrastructure.Persistence.Models;

public partial class OrderRow
{
    public long Id { get; set; }

    public long OrderId { get; set; }

    public string? FortnoxArticleId { get; set; }

    public string? Description { get; set; }

    public decimal Quantity { get; set; }

    public string? Unit { get; set; }

    public decimal Price { get; set; }

    public byte DiscountPercentage { get; set; }

    public short Vat { get; set; }

    public bool HasRut { get; set; }

    public string? InternalNote { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public virtual Order Order { get; set; } = null!;
}
