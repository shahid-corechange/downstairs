using System;
using System.Collections.Generic;

namespace Downstairs.Infrastructure.Persistence.Models;

public partial class OldOrder
{
    public long Id { get; set; }

    public long OldOrderId { get; set; }
}
