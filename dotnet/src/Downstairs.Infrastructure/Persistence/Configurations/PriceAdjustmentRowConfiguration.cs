using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class PriceAdjustmentRowConfiguration : IEntityTypeConfiguration<PriceAdjustmentRow>
{
    public void Configure(EntityTypeBuilder<PriceAdjustmentRow> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("price_adjustment_rows")
            .UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasIndex(e => new { e.AdjustableType, e.AdjustableId }, "price_adjustment_rows_adjustable_type_adjustable_id_index");

        entity.HasIndex(e => e.PriceAdjustmentId, "price_adjustment_rows_price_adjustment_id_foreign");

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.AdjustableId).HasColumnName("adjustable_id");
        entity.Property(e => e.AdjustableType).HasColumnName("adjustable_type");
        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");
        entity.Property(e => e.PreviousPrice)
            .HasColumnType("decimal(8,2) unsigned")
            .HasColumnName("previous_price");
        entity.Property(e => e.Price)
            .HasColumnType("decimal(8,2) unsigned")
            .HasColumnName("price");
        entity.Property(e => e.PriceAdjustmentId).HasColumnName("price_adjustment_id");
        entity.Property(e => e.Status)
            .HasMaxLength(255)
            .HasDefaultValueSql("'pending'")
            .HasColumnName("status");
        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");
        entity.Property(e => e.VatGroup)
            .HasDefaultValueSql("'25'")
            .HasColumnName("vat_group");

        entity.HasOne(d => d.PriceAdjustment).WithMany(p => p.PriceAdjustmentRows)
            .HasForeignKey(d => d.PriceAdjustmentId)
            .HasConstraintName("price_adjustment_rows_price_adjustment_id_foreign");
    }
}