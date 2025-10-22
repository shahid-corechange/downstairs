using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class InvoiceConfiguration : IEntityTypeConfiguration<Invoice>
{
    public void Configure(EntityTypeBuilder<Invoice> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint unsigned")
            .HasColumnName("id");

        entity.Property(e => e.Category)
            .IsRequired()
            .ValueGeneratedOnAdd()
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("category")
            .HasDefaultValueSql("'invoice'");

        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");

        entity.Property(e => e.CustomerId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("customer_id");

        entity.Property(e => e.DeletedAt)
            .HasColumnType("timestamp")
            .HasColumnName("deleted_at");

        entity.Property(e => e.DueAt)
            .HasColumnType("timestamp")
            .HasColumnName("due_at");

        entity.Property(e => e.FortnoxInvoiceId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("fortnox_invoice_id");

        entity.Property(e => e.FortnoxTaxReductionId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("fortnox_tax_reduction_id");

        entity.Property(e => e.Month)
            .HasColumnType("int")
            .HasColumnName("month");

        entity.Property(e => e.Remark)
            .HasColumnType("text")
            .HasColumnName("remark");

        entity.Property(e => e.SentAt)
            .HasColumnType("timestamp")
            .HasColumnName("sent_at");

        entity.Property(e => e.Status)
            .IsRequired()
            .ValueGeneratedOnAdd()
            .HasColumnType("varchar(255)")
            .HasColumnName("status")
            .HasDefaultValueSql("'open'");

        entity.Property(e => e.TotalGross)
            .HasPrecision(12, 2)
            .HasColumnType("decimal(12,2)")
            .HasDefaultValueSql("'0.00'")
            .HasColumnName("total_gross");

        entity.Property(e => e.TotalNet)
            .HasPrecision(12, 2)
            .HasColumnType("decimal(12,2)")
            .HasDefaultValueSql("'0.00'")
            .HasColumnName("total_net");

        entity.Property(e => e.TotalRut)
            .HasPrecision(12, 2)
            .HasColumnType("decimal(12,2)")
            .HasDefaultValueSql("'0.00'")
            .HasColumnName("total_rut");

        entity.Property(e => e.TotalVat)
            .HasPrecision(12, 2)
            .HasColumnType("decimal(12,2)")
            .HasDefaultValueSql("'0.00'")
            .HasColumnName("total_vat");

        entity.Property(e => e.Type)
            .HasColumnType("varchar(255)")
            .HasColumnName("type");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.Property(e => e.UserId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("user_id");

        entity.Property(e => e.Year)
            .HasColumnType("int")
            .HasColumnName("year");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.CustomerId, "invoices_customer_id_foreign");

        entity.HasIndex(e => e.UserId, "invoices_user_id_foreign");

        entity.HasIndex(e => e.Type, "invoices_type_index");

        entity.HasIndex(e => e.Month, "invoices_month_index");

        entity.HasIndex(e => e.Status, "invoices_status_index");

        entity.HasIndex(e => e.Year, "invoices_year_index");

        entity.ToTable("invoices").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.Customer)
            .WithMany(p => p.Invoices)
            .HasForeignKey(d => d.CustomerId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("invoices_customer_id_foreign");

        entity.HasOne(d => d.User)
            .WithMany(p => p.Invoices)
            .HasForeignKey(d => d.UserId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("invoices_user_id_foreign");
    }
}