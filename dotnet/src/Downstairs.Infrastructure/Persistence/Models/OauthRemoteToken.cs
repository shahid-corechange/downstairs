using System;
using System.Collections.Generic;

namespace Downstairs.Infrastructure.Persistence.Models;

public partial class OauthRemoteToken
{
    public long Id { get; set; }

    public string AppName { get; set; } = null!;

    public string TokenType { get; set; } = null!;

    public string Scope { get; set; } = null!;

    public string AccessToken { get; set; } = null!;

    public string RefreshToken { get; set; } = null!;

    public DateTime? AccessExpiresAt { get; set; }

    public DateTime? RefreshExpiresAt { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }
}
