using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class ScheduleCleaningProductConfiguration : IEntityTypeConfiguration<ScheduleCleaningProduct>
{
    public void Configure(EntityTypeBuilder<ScheduleCleaningProduct> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("schedule_cleaning_products")
            .UseCollation("utf8mb4_unicode_ci");

        entity.HasIndex(e => e.ProductId, "schedule_cleaning_products_product_id_foreign");

        entity.HasIndex(e => e.ScheduleCleaningId, "schedule_cleaning_products_schedule_cleaning_id_foreign");

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");
        entity.Property(e => e.DiscountPercentage).HasColumnName("discount_percentage");
        entity.Property(e => e.PaymentMethod)
            .HasMaxLength(255)
            .HasDefaultValueSql("'invoice'")
            .HasColumnName("payment_method");
        entity.Property(e => e.Price)
            .HasColumnType("decimal(8,2) unsigned")
            .HasColumnName("price");
        entity.Property(e => e.ProductId).HasColumnName("product_id");
        entity.Property(e => e.Quantity)
            .HasColumnType("decimal(8,2) unsigned")
            .HasColumnName("quantity");
        entity.Property(e => e.ScheduleCleaningId).HasColumnName("schedule_cleaning_id");
        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.HasOne(d => d.Product).WithMany(p => p.ScheduleCleaningProducts)
            .HasForeignKey(d => d.ProductId)
            .HasConstraintName("schedule_cleaning_products_product_id_foreign");

        entity.HasOne(d => d.ScheduleCleaning).WithMany(p => p.ScheduleCleaningProducts)
            .HasForeignKey(d => d.ScheduleCleaningId)
            .HasConstraintName("schedule_cleaning_products_schedule_cleaning_id_foreign");
    }
}

