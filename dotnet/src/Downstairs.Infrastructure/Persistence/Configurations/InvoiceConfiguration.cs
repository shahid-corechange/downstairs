using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class InvoiceConfiguration : IEntityTypeConfiguration<Invoice>
{
    public void Configure(EntityTypeBuilder<Invoice> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("invoices")
            .UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasIndex(e => e.CustomerId, "invoices_customer_id_foreign");

        entity.HasIndex(e => e.Month, "invoices_month_index");

        entity.HasIndex(e => e.Status, "invoices_status_index");

        entity.HasIndex(e => e.Type, "invoices_type_index");

        entity.HasIndex(e => e.UserId, "invoices_user_id_foreign");

        entity.HasIndex(e => e.Year, "invoices_year_index");

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");
        entity.Property(e => e.CustomerId).HasColumnName("customer_id");
        entity.Property(e => e.DeletedAt)
            .HasColumnType("timestamp")
            .HasColumnName("deleted_at");
        entity.Property(e => e.DueAt)
            .HasColumnType("timestamp")
            .HasColumnName("due_at");
        entity.Property(e => e.FortnoxInvoiceId).HasColumnName("fortnox_invoice_id");
        entity.Property(e => e.FortnoxTaxReductionId).HasColumnName("fortnox_tax_reduction_id");
        entity.Property(e => e.Month).HasColumnName("month");
        entity.Property(e => e.Remark)
            .HasColumnType("text")
            .HasColumnName("remark");
        entity.Property(e => e.SentAt)
            .HasColumnType("timestamp")
            .HasColumnName("sent_at");
        entity.Property(e => e.Status)
            .HasDefaultValueSql("'open'")
            .HasColumnName("status");
        entity.Property(e => e.TotalGross)
            .HasPrecision(12, 2)
            .HasColumnName("total_gross");
        entity.Property(e => e.TotalNet)
            .HasPrecision(12, 2)
            .HasColumnName("total_net");
        entity.Property(e => e.TotalRut)
            .HasPrecision(12, 2)
            .HasColumnName("total_rut");
        entity.Property(e => e.TotalVat)
            .HasPrecision(12, 2)
            .HasColumnName("total_vat");
        entity.Property(e => e.Type).HasColumnName("type");
        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");
        entity.Property(e => e.UserId).HasColumnName("user_id");
        entity.Property(e => e.Year).HasColumnName("year");

        entity.HasOne(d => d.Customer).WithMany(p => p.Invoices)
            .HasForeignKey(d => d.CustomerId)
            .HasConstraintName("invoices_customer_id_foreign");

        entity.HasOne(d => d.User).WithMany(p => p.Invoices)
            .HasForeignKey(d => d.UserId)
            .HasConstraintName("invoices_user_id_foreign");
    }
}