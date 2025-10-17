using System;
using System.Collections.Generic;

namespace Downstairs.Infrastructure.Persistence.Models;

public partial class PriceAdjustment
{
    public long Id { get; set; }

    public long CauserId { get; set; }

    public string Type { get; set; } = null!;

    public string? Description { get; set; }

    public string PriceType { get; set; } = null!;

    public decimal Price { get; set; }

    public DateOnly ExecutionDate { get; set; }

    public string Status { get; set; } = null!;

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public DateTime? DeletedAt { get; set; }

    public virtual User Causer { get; set; } = null!;

    public virtual ICollection<PriceAdjustmentRow> PriceAdjustmentRows { get; set; } = new List<PriceAdjustmentRow>();
}
