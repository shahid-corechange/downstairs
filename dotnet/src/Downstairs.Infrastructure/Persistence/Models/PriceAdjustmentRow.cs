using System;
using System.Collections.Generic;

namespace Downstairs.Infrastructure.Persistence.Models;

public partial class PriceAdjustmentRow
{
    public long Id { get; set; }

    public long PriceAdjustmentId { get; set; }

    public string AdjustableType { get; set; } = null!;

    public long AdjustableId { get; set; }

    public decimal PreviousPrice { get; set; }

    public decimal Price { get; set; }

    public byte VatGroup { get; set; }

    public string Status { get; set; } = null!;

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public virtual PriceAdjustment PriceAdjustment { get; set; } = null!;
}
