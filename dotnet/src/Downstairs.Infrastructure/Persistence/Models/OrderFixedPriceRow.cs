using System;
using System.Collections.Generic;

namespace Downstairs.Infrastructure.Persistence.Models;

public partial class OrderFixedPriceRow
{
    public long Id { get; set; }

    public long OrderFixedPriceId { get; set; }

    public string Type { get; set; } = null!;

    public string? Description { get; set; }

    public uint Quantity { get; set; }

    public decimal Price { get; set; }

    public byte VatGroup { get; set; }

    public sbyte HasRut { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public virtual OrderFixedPrice OrderFixedPrice { get; set; } = null!;
}
