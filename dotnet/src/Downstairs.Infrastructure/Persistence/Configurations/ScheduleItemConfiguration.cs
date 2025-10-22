using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class ScheduleItemConfiguration : IEntityTypeConfiguration<ScheduleItem>
{
    public void Configure(EntityTypeBuilder<ScheduleItem> entity)
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
            .HasDefaultValueSql("'0'")
            .HasColumnName("discount_percentage");

        entity.Property(e => e.ItemableId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("itemable_id");

        entity.Property(e => e.ItemableType)
            .IsRequired()
            .HasColumnType("varchar(255)")
            .HasColumnName("itemable_type");

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

        entity.Property(e => e.Quantity)
            .ValueGeneratedOnAdd()
            .HasColumnType("decimal(8,2) unsigned")
            .HasColumnName("quantity")
            .HasDefaultValueSql("'1.00'");

        entity.Property(e => e.ScheduleId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("schedule_id");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => new { e.ItemableType, e.ItemableId }, "schedule_items_itemable_type_itemable_id_index");

        entity.HasIndex(e => e.ScheduleId, "schedule_items_schedule_id_foreign");

        entity.ToTable("schedule_items").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.Schedule)
            .WithMany(p => p.ScheduleItems)
            .HasForeignKey(d => d.ScheduleId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("schedule_items_schedule_id_foreign");
    }
}