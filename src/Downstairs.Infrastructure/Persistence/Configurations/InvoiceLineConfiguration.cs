using Downstairs.Domain.Entities;
using Downstairs.Domain.ValueObjects;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

/// <summary>
/// Entity Framework configuration for InvoiceLine entity
/// </summary>
public class InvoiceLineConfiguration : IEntityTypeConfiguration<InvoiceLine>
{
    public void Configure(EntityTypeBuilder<InvoiceLine> builder)
    {
        builder.ToTable("invoice_lines");

        builder.HasKey(x => x.Id);

        builder.Property(x => x.Id)
            .HasColumnName("id");

        builder.Property(x => x.InvoiceId)
            .HasColumnName("invoice_id")
            .IsRequired();

        builder.Property(x => x.Description)
            .HasColumnName("description")
            .HasMaxLength(500)
            .IsRequired();

        builder.Property(x => x.Quantity)
            .HasColumnName("quantity")
            .IsRequired();

        // Configure UnitPrice as owned type
        builder.OwnsOne(x => x.UnitPrice, money =>
        {
            money.Property(m => m.Amount)
                .HasColumnName("unit_price")
                .HasPrecision(18, 2);

            money.Property(m => m.Currency)
                .HasColumnName("currency")
                .HasMaxLength(3);
        });

        builder.Property(x => x.VatRate)
            .HasColumnName("vat_rate")
            .HasPrecision(5, 4);

        // TotalPrice is a computed property, so we ignore it
        builder.Ignore(x => x.TotalPrice);

        // Ignore domain events
        builder.Ignore(x => x.DomainEvents);
    }
}