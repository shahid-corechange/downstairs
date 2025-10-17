using System;
using System.Collections.Generic;

namespace Downstairs.Infrastructure.Persistence.Models;

public partial class ModelHasPermission
{
    public long PermissionId { get; set; }

    public string ModelType { get; set; } = null!;

    public long ModelId { get; set; }

    public virtual Permission Permission { get; set; } = null!;
}
