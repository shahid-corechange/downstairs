using Downstairs.Domain.Entities;
using Downstairs.Domain.Enums;
using Downstairs.Domain.ValueObjects;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

/// <summary>
/// Entity Framework configuration for Invoice entity
/// </summary>
public class InvoiceConfiguration : IEntityTypeConfiguration<Invoice>
{
    public void Configure(EntityTypeBuilder<Invoice> builder)
    {
        builder.ToTable("invoices");

        builder.HasKey(x => x.Id);

        builder.Property(x => x.Id)
            .HasColumnName("id");

        builder.Property(x => x.InvoiceNumber)
            .HasColumnName("invoice_number")
            .HasMaxLength(50)
            .IsRequired();

        builder.HasIndex(x => x.InvoiceNumber)
            .IsUnique();

        builder.Property(x => x.CustomerId)
            .HasColumnName("customer_id")
            .IsRequired();

        // Configure Money as owned type
        builder.OwnsOne(x => x.TotalAmount, money =>
        {
            money.Property(m => m.Amount)
                .HasColumnName("total_amount")
                .HasPrecision(18, 2);

            money.Property(m => m.Currency)
                .HasColumnName("currency")
                .HasMaxLength(3);
        });

        builder.Property(x => x.InvoiceDate)
            .HasColumnName("invoice_date");

        builder.Property(x => x.DueDate)
            .HasColumnName("due_date");

        builder.Property(x => x.Status)
            .HasColumnName("status")
            .HasConversion(
                v => v.ToString(),
                v => (InvoiceStatus)Enum.Parse(typeof(InvoiceStatus), v));

        builder.Property(x => x.FortnoxInvoiceNumber)
            .HasColumnName("fortnox_invoice_number")
            .HasMaxLength(50);

        builder.Property(x => x.SentToKivra)
            .HasColumnName("sent_to_kivra");

        builder.Property(x => x.KivraSentAt)
            .HasColumnName("kivra_sent_at");

        builder.Property(x => x.CreatedAt)
            .HasColumnName("created_at");

        builder.Property(x => x.UpdatedAt)
            .HasColumnName("updated_at");

        // Configure relationships
        builder.HasOne(x => x.Customer)
            .WithMany(c => c.Invoices)
            .HasForeignKey(x => x.CustomerId)
            .OnDelete(DeleteBehavior.Restrict);

        builder.HasMany(x => x.Lines)
            .WithOne()
            .HasForeignKey(l => l.InvoiceId)
            .OnDelete(DeleteBehavior.Cascade);

        // Ignore domain events
        builder.Ignore(x => x.DomainEvents);
    }
}