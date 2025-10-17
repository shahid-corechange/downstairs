using System;
using System.Collections.Generic;

namespace Downstairs.Infrastructure.Persistence.Models;

public partial class UserOtp
{
    public long Id { get; set; }

    public long UserId { get; set; }

    public string Otp { get; set; } = null!;

    public string Info { get; set; } = null!;

    public DateTime? ExpireAt { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }
}
