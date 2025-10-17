namespace Downstairs.Infrastructure.Persistence.Models;

public partial class CreditCreditTransaction
{
    public long Id { get; set; }

    public long CreditId { get; set; }

    public long CreditTransactionId { get; set; }

    public byte Amount { get; set; }

    public virtual Credit Credit { get; set; } = null!;

    public virtual CreditTransaction CreditTransaction { get; set; } = null!;
}