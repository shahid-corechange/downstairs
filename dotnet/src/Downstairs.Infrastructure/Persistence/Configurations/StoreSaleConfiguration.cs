using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class StoreSaleConfiguration : IEntityTypeConfiguration<StoreSale>
{
    public void Configure(EntityTypeBuilder<StoreSale> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint")
            .HasColumnName("id");

        entity.Property(e => e.CauserId)
            .HasColumnType("bigint")
            .HasColumnName("causer_id");

        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");

        entity.Property(e => e.DeletedAt)
            .HasColumnType("timestamp")
            .HasColumnName("deleted_at");

        entity.Property(e => e.PaymentMethod)
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("payment_method");

        entity.Property(e => e.Status)
            .IsRequired()
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("status");

        entity.Property(e => e.StoreId)
            .HasColumnType("bigint")
            .HasColumnName("store_id");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.CauserId, "store_sales_causer_id_foreign");

        entity.HasIndex(e => e.StoreId, "store_sales_store_id_foreign");

        entity.ToTable("store_sales").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.Causer)
            .WithMany(p => p.StoreSales)
            .HasForeignKey(d => d.CauserId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("store_sales_causer_id_foreign");

        entity.HasOne(d => d.Store)
            .WithMany(p => p.StoreSales)
            .HasForeignKey(d => d.StoreId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("store_sales_store_id_foreign");
    }
}