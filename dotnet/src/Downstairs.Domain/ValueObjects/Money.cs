using Downstairs.Domain.Shared;

namespace Downstairs.Domain.ValueObjects;

/// <summary>
/// Money value object representing monetary amounts with currency
/// </summary>
public record Money : ValueObject
{
    public decimal Amount
    {
        get; init;
    }
    public string Currency { get; init; } = DomainConstants.Currency.Default;

    public Money() { }

    public Money(decimal amount, string currency = DomainConstants.Currency.Default)
    {
        if (amount < 0)
        {
            throw new ArgumentException("Amount cannot be negative", nameof(amount));
        }

        Amount = amount;
        Currency = currency ?? throw new ArgumentNullException(nameof(currency));
    }

    public static Money FromSEK(decimal amount) => new(amount, DomainConstants.Currency.SEK);

    public static Money operator +(Money left, Money right)
    {
        if (left.Currency != right.Currency)
        {
            throw new InvalidOperationException("Cannot add money with different currencies");
        }

        return new Money(left.Amount + right.Amount, left.Currency);
    }

    public static Money operator -(Money left, Money right)
    {
        if (left.Currency != right.Currency)
        {
            throw new InvalidOperationException("Cannot subtract money with different currencies");
        }

        return new Money(left.Amount - right.Amount, left.Currency);
    }

    public static Money operator *(Money money, decimal multiplier)
    {
        return new Money(money.Amount * multiplier, money.Currency);
    }

    public override string ToString()
    {
        return $"{Amount:C} {Currency}";
    }
}