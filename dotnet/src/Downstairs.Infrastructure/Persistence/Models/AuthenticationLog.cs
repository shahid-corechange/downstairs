namespace Downstairs.Infrastructure.Persistence.Models;

public partial class AuthenticationLog
{
    public long Id { get; set; }

    public string AuthenticatableType { get; set; } = null!;

    public long AuthenticatableId { get; set; }

    public string? IpAddress { get; set; }

    public string? UserAgent { get; set; }

    public DateTime? LoginAt { get; set; }

    public bool LoginSuccessful { get; set; }

    public DateTime? LogoutAt { get; set; }

    public bool ClearedByUser { get; set; }

    public string? Location { get; set; }
}