using System;
using System.Collections.Generic;

namespace Downstairs.Infrastructure.Persistence.Models;

public partial class Permission
{
    public long Id { get; set; }

    public string Name { get; set; } = null!;

    public string GuardName { get; set; } = null!;

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public virtual ICollection<ModelHasPermission> ModelHasPermissions { get; set; } = new List<ModelHasPermission>();

    public virtual ICollection<Role> Roles { get; set; } = new List<Role>();
}
