namespace Downstairs.Infrastructure.Persistence.Models;

public partial class Credit
{
    public long Id { get; set; }

    public long UserId { get; set; }

    public long? ScheduleCleaningId { get; set; }

    public long? ScheduleId { get; set; }

    public long? IssuerId { get; set; }

    public byte InitialAmount { get; set; }

    public byte RemainingAmount { get; set; }

    public string Type { get; set; } = null!;

    public string Description { get; set; } = null!;

    public DateTime ValidUntil { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public virtual ICollection<CreditCreditTransaction> CreditCreditTransactions { get; set; } = new List<CreditCreditTransaction>();

    public virtual User? Issuer { get; set; }

    public virtual Schedule? Schedule { get; set; }

    public virtual ScheduleCleaning? ScheduleCleaning { get; set; }

    public virtual User User { get; set; } = null!;
}