using System;
using System.Collections.Generic;

namespace Downstairs.Infrastructure.Persistence.Models;

public partial class ScheduleCleaningProduct
{
    public long Id { get; set; }

    public long ScheduleCleaningId { get; set; }

    public long ProductId { get; set; }

    public decimal Price { get; set; }

    public decimal Quantity { get; set; }

    public byte DiscountPercentage { get; set; }

    public string PaymentMethod { get; set; } = null!;

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public virtual Product Product { get; set; } = null!;

    public virtual ScheduleCleaning ScheduleCleaning { get; set; } = null!;
}
