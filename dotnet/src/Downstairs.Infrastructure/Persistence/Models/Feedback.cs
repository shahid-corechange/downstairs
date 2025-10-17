namespace Downstairs.Infrastructure.Persistence.Models;

public partial class Feedback
{
    public long Id { get; set; }

    public string FeedbackableType { get; set; } = null!;

    public long FeedbackableId { get; set; }

    public string Option { get; set; } = null!;

    public string Description { get; set; } = null!;

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public DateTime? DeletedAt { get; set; }
}