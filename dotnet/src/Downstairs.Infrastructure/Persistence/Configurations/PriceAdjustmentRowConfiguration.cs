using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class PriceAdjustmentRowConfiguration : IEntityTypeConfiguration<PriceAdjustmentRow>
{
    public void Configure(EntityTypeBuilder<PriceAdjustmentRow> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint")
            .HasColumnName("id");

        entity.Property(e => e.AdjustableId)
            .HasColumnType("bigint")
            .HasColumnName("adjustable_id");

        entity.Property(e => e.AdjustableType)
            .IsRequired()
            .HasColumnType("varchar(255)")
            .HasColumnName("adjustable_type");

        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");

        entity.Property(e => e.PreviousPrice)
            .HasColumnType("decimal(8,2)")
            .HasColumnName("previous_price");

        entity.Property(e => e.Price)
            .HasColumnType("decimal(8,2)")
            .HasColumnName("price");

        entity.Property(e => e.PriceAdjustmentId)
            .HasColumnType("bigint")
            .HasColumnName("price_adjustment_id");

        entity.Property(e => e.Status)
            .IsRequired()
            .ValueGeneratedOnAdd()
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("status")
            .HasDefaultValueSql("'pending'");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.Property(e => e.VatGroup)
            .ValueGeneratedOnAdd()
            .HasColumnType("tinyint")
            .HasColumnName("vat_group")
            .HasDefaultValueSql("'25'");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => new { e.AdjustableType, e.AdjustableId }, "price_adjustment_rows_adjustable_type_adjustable_id_index");

        entity.HasIndex(e => e.PriceAdjustmentId, "price_adjustment_rows_price_adjustment_id_foreign");

        entity.ToTable("price_adjustment_rows").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.PriceAdjustment)
            .WithMany(p => p.PriceAdjustmentRows)
            .HasForeignKey(d => d.PriceAdjustmentId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("price_adjustment_rows_price_adjustment_id_foreign");
    }
}