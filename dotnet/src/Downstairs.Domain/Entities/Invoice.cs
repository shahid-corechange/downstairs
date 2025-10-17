using Downstairs.Domain.Enums;
using Downstairs.Domain.Events;
using Downstairs.Domain.Shared;
using Downstairs.Domain.ValueObjects;

namespace Downstairs.Domain.Entities;

/// <summary>
/// Invoice entity representing billing documents with Kivra delivery integration
/// </summary>
public class Invoice : Entity<long>
{
    public string InvoiceNumber { get; private set; } = string.Empty;
    public long CustomerId { get; private set; }
    public Customer Customer { get; private set; } = null!;
    public Money TotalAmount { get; private set; } = null!;
    public decimal TotalGross { get; private set; }
    public decimal TotalNet { get; private set; }
    public decimal TotalVat { get; private set; }
    public decimal TotalRut { get; private set; }
    public long UserId { get; private set; }
    public long? FortnoxInvoiceId { get; private set; }
    public long? FortnoxTaxReductionId { get; private set; }
    public string? Type { get; private set; }
    public int Month { get; private set; }
    public int Year { get; private set; }
    public string? Remark { get; private set; }
    public DateOnly InvoiceDate { get; private set; }
    public DateOnly DueDate { get; private set; }
    public InvoiceStatus Status { get; private set; }
    public string StatusText { get; private set; } = InvoiceStatus.Draft.ToString();
    public string? FortnoxInvoiceNumber { get; private set; }
    public bool SentToKivra { get; private set; }
    public DateTimeOffset? KivraSentAt { get; private set; }
    public DateTimeOffset? SentAt { get; private set; }
    public DateTimeOffset? DueAtRaw { get; private set; }
    public DateTimeOffset? DeletedAt { get; private set; }
    public DateTimeOffset CreatedAt { get; private set; }
    public DateTimeOffset? UpdatedAt { get; private set; }

    private readonly List<InvoiceLine> _lines = [];
    public IReadOnlyCollection<InvoiceLine> Lines => _lines.AsReadOnly();

    private Invoice() : base() { } // EF Core

    private Invoice(
        string invoiceNumber,
        long customerId,
        Money totalAmount,
        DateOnly invoiceDate,
        DateOnly dueDate)
    {
        InvoiceNumber = invoiceNumber;
        CustomerId = customerId;
        TotalAmount = totalAmount;
        TotalNet = totalAmount.Amount;
        TotalGross = totalAmount.Amount;
        TotalVat = 0m;
        TotalRut = 0m;
        InvoiceDate = invoiceDate;
        DueDate = dueDate;
        Status = InvoiceStatus.Draft;
        StatusText = InvoiceStatus.Draft.ToString();
        Remark = invoiceNumber;
        var dueDateTime = dueDate.ToDateTime(TimeOnly.MinValue);
        DueAtRaw = new DateTimeOffset(DateTime.SpecifyKind(dueDateTime, DateTimeKind.Utc));
        CreatedAt = DateTimeOffset.UtcNow;
    }

    public static Invoice Create(
        string invoiceNumber,
        long customerId,
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
        if (long.TryParse(fortnoxInvoiceNumber, out var parsed))
        {
            FortnoxInvoiceId = parsed;
        }
        UpdatedAt = DateTimeOffset.UtcNow;
    }

    public void MarkAsSent()
    {
        Status = InvoiceStatus.Sent;
        StatusText = InvoiceStatus.Sent.ToString();
        SentAt = DateTimeOffset.UtcNow;
        UpdatedAt = DateTimeOffset.UtcNow;
    }

    public void MarkAsPaid()
    {
        Status = InvoiceStatus.Paid;
        StatusText = InvoiceStatus.Paid.ToString();
        UpdatedAt = DateTimeOffset.UtcNow;
    }

    public void MarkAsSentToKivra()
    {
        SentToKivra = true;
        KivraSentAt = DateTimeOffset.UtcNow;
        SentAt ??= KivraSentAt;
        UpdatedAt = DateTimeOffset.UtcNow;

        AddDomainEvent(new InvoiceSentToKivraEvent(Id, CustomerId, InvoiceNumber));
    }

    internal static Invoice FromPersistence(
        long id,
        string invoiceNumber,
        long customerId,
        decimal totalNet,
        decimal totalGross,
        decimal totalVat,
        decimal totalRut,
        string statusText,
        DateTimeOffset createdAt,
        DateTimeOffset? updatedAt,
        DateTimeOffset? sentAt,
        DateTimeOffset? dueAt,
        DateTimeOffset? deletedAt,
        long userId,
        long? fortnoxInvoiceId,
        long? fortnoxTaxReductionId,
        string? type,
        int month,
        int year,
        string? remark,
        string? fortnoxInvoiceNumber)
    {
        var invoice = new Invoice
        {
            Id = id,
            InvoiceNumber = invoiceNumber,
            CustomerId = customerId,
            TotalAmount = new Money(totalNet, DomainConstants.Currency.SEK),
            TotalNet = totalNet,
            TotalGross = totalGross,
            TotalVat = totalVat,
            TotalRut = totalRut,
            Status = ParseStatus(statusText)
        };
        invoice.StatusText = string.IsNullOrWhiteSpace(statusText) ? invoice.Status.ToString() : statusText;
        invoice.CreatedAt = createdAt;
        invoice.UpdatedAt = updatedAt;
        invoice.SentAt = sentAt;
        invoice.DueAtRaw = dueAt;
        invoice.DeletedAt = deletedAt;
        invoice.InvoiceDate = DateOnly.FromDateTime(createdAt.UtcDateTime);
        invoice.DueDate = dueAt.HasValue ? DateOnly.FromDateTime(dueAt.Value.UtcDateTime) : invoice.InvoiceDate.AddDays(DomainConstants.Invoice.DefaultDueDays);
        invoice.UserId = userId;
        invoice.FortnoxInvoiceId = fortnoxInvoiceId;
        invoice.FortnoxTaxReductionId = fortnoxTaxReductionId;
        invoice.Type = type;
        invoice.Month = month;
        invoice.Year = year;
        invoice.Remark = remark;
        invoice.FortnoxInvoiceNumber = fortnoxInvoiceNumber ?? fortnoxInvoiceId?.ToString();
        invoice.SentToKivra = sentAt is not null;
        invoice.KivraSentAt = sentAt;

        return invoice;
    }

    internal void ApplyUserContext(long userId, string? type, int month, int year)
    {
        UserId = userId;
        Type = type;
        Month = month;
        Year = year;
    }

    internal void ApplyTotals(decimal totalNet, decimal totalGross, decimal totalVat, decimal totalRut)
    {
        TotalNet = totalNet;
        TotalGross = totalGross;
        TotalVat = totalVat;
        TotalRut = totalRut;
        var currency = TotalAmount?.Currency ?? DomainConstants.Currency.Default;
        TotalAmount = new Money(totalNet, currency);
    }

    private static InvoiceStatus ParseStatus(string statusText)
    {
        if (Enum.TryParse<InvoiceStatus>(statusText, ignoreCase: true, out var parsed))
        {
            return parsed;
        }

        return InvoiceStatus.Draft;
    }
}