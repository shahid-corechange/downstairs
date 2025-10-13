using Downstairs.Domain.Shared;
using Downstairs.Domain.ValueObjects;
using Downstairs.Domain.Events;
using Downstairs.Domain.Enums;

namespace Downstairs.Domain.Entities;

/// <summary>
/// Invoice entity representing billing documents with Kivra delivery integration
/// </summary>
public class Invoice : Entity<Guid>
{
    public string InvoiceNumber { get; private set; } = string.Empty;
    public Guid CustomerId { get; private set; }
    public Customer Customer { get; private set; } = null!;
    public Money TotalAmount { get; private set; } = null!;
    public DateOnly InvoiceDate { get; private set; }
    public DateOnly DueDate { get; private set; }
    public InvoiceStatus Status { get; private set; }
    public string? FortnoxInvoiceNumber { get; private set; }
    public bool SentToKivra { get; private set; }
    public DateTimeOffset? KivraSentAt { get; private set; }
    public DateTimeOffset CreatedAt { get; private set; }
    public DateTimeOffset? UpdatedAt { get; private set; }

    private readonly List<InvoiceLine> _lines = [];
    public IReadOnlyCollection<InvoiceLine> Lines => _lines.AsReadOnly();

    private Invoice() : base() { } // EF Core

    private Invoice(
        string invoiceNumber,
        Guid customerId,
        Money totalAmount,
        DateOnly invoiceDate,
        DateOnly dueDate) : base(Guid.NewGuid())
    {
        InvoiceNumber = invoiceNumber;
        CustomerId = customerId;
        TotalAmount = totalAmount;
        InvoiceDate = invoiceDate;
        DueDate = dueDate;
        Status = InvoiceStatus.Draft;
        CreatedAt = DateTimeOffset.UtcNow;
    }

    public static Invoice Create(
        string invoiceNumber,
        Guid customerId,
        Money totalAmount,
        DateOnly invoiceDate,
        DateOnly dueDate,
        IEnumerable<InvoiceLine> lines)
    {
        var invoice = new Invoice(invoiceNumber, customerId, totalAmount, invoiceDate, dueDate);
        
        foreach (var line in lines)
        {
            invoice._lines.Add(line);
        }

        invoice.AddDomainEvent(new InvoiceCreatedEvent(
            invoice.Id,
            invoice.CustomerId,
            invoice.InvoiceNumber,
            invoice.TotalAmount.Amount));

        return invoice;
    }

    public void SetFortnoxInvoiceNumber(string fortnoxInvoiceNumber)
    {
        FortnoxInvoiceNumber = fortnoxInvoiceNumber;
        UpdatedAt = DateTimeOffset.UtcNow;
    }

    public void MarkAsSent()
    {
        Status = InvoiceStatus.Sent;
        UpdatedAt = DateTimeOffset.UtcNow;
    }

    public void MarkAsPaid()
    {
        Status = InvoiceStatus.Paid;
        UpdatedAt = DateTimeOffset.UtcNow;
    }

    public void MarkAsSentToKivra()
    {
        SentToKivra = true;
        KivraSentAt = DateTimeOffset.UtcNow;
        UpdatedAt = DateTimeOffset.UtcNow;

        AddDomainEvent(new InvoiceSentToKivraEvent(Id, CustomerId, InvoiceNumber));
    }
}