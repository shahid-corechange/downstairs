namespace Downstairs.Infrastructure.Persistence.Models;

public partial class Employee
{
    public long Id { get; set; }

    public string? FortnoxId { get; set; }

    public long UserId { get; set; }

    public long AddressId { get; set; }

    public string IdentityNumber { get; set; } = null!;

    public string Name { get; set; } = null!;

    public string Email { get; set; } = null!;

    public string? Phone1 { get; set; }

    public string? DialCode { get; set; }

    public bool IsValidIdentity { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public DateTime? DeletedAt { get; set; }

    public virtual Address Address { get; set; } = null!;

    public virtual ICollection<LeaveRegistration> LeaveRegistrations { get; set; } = new List<LeaveRegistration>();

    public virtual User User { get; set; } = null!;
}