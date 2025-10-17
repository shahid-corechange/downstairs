using System;
using System.Collections.Generic;

namespace Downstairs.Infrastructure.Persistence.Models;

public partial class SubscriptionStaffDetail
{
    public long Id { get; set; }

    public long SubscriptionId { get; set; }

    public long UserId { get; set; }

    public int Quarters { get; set; }

    public bool? IsActive { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public DateTime? DeletedAt { get; set; }

    public virtual Subscription Subscription { get; set; } = null!;

    public virtual User User { get; set; } = null!;
}
