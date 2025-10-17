using System;
using System.Collections.Generic;

namespace Downstairs.Infrastructure.Persistence.Models;

public partial class OldCustomer
{
    public long MyRowId { get; set; }

    public long CustomerId { get; set; }

    public long OldCustomerId { get; set; }

    public virtual Customer Customer { get; set; } = null!;
}
