using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class ScheduleCleaningProductConfiguration : IEntityTypeConfiguration<ScheduleCleaningProduct>
{
    public void Configure(EntityTypeBuilder<ScheduleCleaningProduct> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint unsigned")
            .HasColumnName("id");

        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");

        entity.Property(e => e.DiscountPercentage)
            .HasColumnType("tinyint unsigned")
            .HasColumnName("discount_percentage")
            .HasDefaultValueSql("'0'");

        entity.Property(e => e.PaymentMethod)
            .IsRequired()
            .ValueGeneratedOnAdd()
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("payment_method")
            .HasDefaultValueSql("'invoice'");

        entity.Property(e => e.Price)
            .HasColumnType("decimal(8,2) unsigned")
            .HasColumnName("price");

        entity.Property(e => e.ProductId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("product_id");

        entity.Property(e => e.Quantity)
            .HasColumnType("decimal(8,2) unsigned")
            .HasColumnName("quantity");

        entity.Property(e => e.ScheduleCleaningId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("schedule_cleaning_id");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.ProductId, "schedule_cleaning_products_product_id_foreign");

        entity.HasIndex(e => e.ScheduleCleaningId, "schedule_cleaning_products_schedule_cleaning_id_foreign");

        entity.ToTable("schedule_cleaning_products").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.Product)
            .WithMany(p => p.ScheduleCleaningProducts)
            .HasForeignKey(d => d.ProductId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("schedule_cleaning_products_product_id_foreign");

        entity.HasOne(d => d.ScheduleCleaning)
            .WithMany(p => p.ScheduleCleaningProducts)
            .HasForeignKey(d => d.ScheduleCleaningId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("schedule_cleaning_products_schedule_cleaning_id_foreign");
    }
}