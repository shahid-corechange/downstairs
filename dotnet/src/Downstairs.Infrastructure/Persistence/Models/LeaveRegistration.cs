namespace Downstairs.Infrastructure.Persistence.Models;

public partial class LeaveRegistration
{
    public long Id { get; set; }

    public long EmployeeId { get; set; }

    public string Type { get; set; } = null!;

    public DateTime StartAt { get; set; }

    public DateTime? EndAt { get; set; }

    public bool IsStopped { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public DateTime? DeletedAt { get; set; }

    public virtual Employee Employee { get; set; } = null!;

    public virtual ICollection<LeaveRegistrationDetail> LeaveRegistrationDetails { get; set; } = new List<LeaveRegistrationDetail>();
}