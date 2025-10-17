using Downstairs.Domain.Shared;
using Downstairs.Domain.ValueObjects;

namespace Downstairs.Domain.Entities;

/// <summary>
/// Invoice line item entity
/// </summary>
public class InvoiceLine : Entity<long>
{
    public long InvoiceId { get; private set; }
    public string Description { get; private set; } = string.Empty;
    public int Quantity { get; private set; }
    public Money UnitPrice { get; private set; } = null!;
    public Money TotalPrice => UnitPrice * Quantity;
    public decimal VatRate { get; private set; } = 0.25m; // 25% Swedish VAT

    private InvoiceLine() : base() { } // EF Core

    public InvoiceLine(
        string description,
        int quantity,
        Money unitPrice,
        decimal vatRate = 0.25m)
    {
        Description = description;
        Quantity = quantity;
        UnitPrice = unitPrice;
        VatRate = vatRate;
    }
}