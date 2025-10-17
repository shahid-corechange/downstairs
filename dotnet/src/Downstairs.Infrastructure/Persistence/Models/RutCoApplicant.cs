namespace Downstairs.Infrastructure.Persistence.Models;

public partial class RutCoApplicant
{
    public long Id { get; set; }

    public long UserId { get; set; }

    public string Name { get; set; } = null!;

    public string IdentityNumber { get; set; } = null!;

    public string? Phone { get; set; }

    public string? DialCode { get; set; }

    public DateOnly? PauseStartDate { get; set; }

    public DateOnly? PauseEndDate { get; set; }

    public bool IsEnabled { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public DateTime? DeletedAt { get; set; }

    public virtual User User { get; set; } = null!;
}