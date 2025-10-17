using System;
using System.Collections.Generic;

namespace Downstairs.Infrastructure.Persistence.Models;

public partial class UserInfo
{
    public long Id { get; set; }

    public long UserId { get; set; }

    public string? Avatar { get; set; }

    public string? Language { get; set; }

    public string? Timezone { get; set; }

    public string? Currency { get; set; }

    public string? NotificationMethod { get; set; }

    public string TwoFactorAuth { get; set; } = null!;

    public sbyte? Marketing { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public DateTime? DeletedAt { get; set; }
}
