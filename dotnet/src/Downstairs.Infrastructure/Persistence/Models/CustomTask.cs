namespace Downstairs.Infrastructure.Persistence.Models;

public partial class CustomTask
{
    public long Id { get; set; }

    public string TaskableType { get; set; } = null!;

    public long TaskableId { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public virtual ICollection<ScheduleTask> ScheduleTasks { get; set; } = new List<ScheduleTask>();

    public virtual ICollection<Task> Tasks { get; set; } = new List<Task>();
}