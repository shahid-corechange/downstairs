using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class PriceAdjustmentConfiguration : IEntityTypeConfiguration<PriceAdjustment>
{
    public void Configure(EntityTypeBuilder<PriceAdjustment> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("price_adjustments")
            .UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasIndex(e => e.CauserId, "price_adjustments_causer_id_foreign");

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.CauserId).HasColumnName("causer_id");
        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");
        entity.Property(e => e.DeletedAt)
            .HasColumnType("timestamp")
            .HasColumnName("deleted_at");
        entity.Property(e => e.Description)
            .HasMaxLength(255)
            .HasColumnName("description");
        entity.Property(e => e.ExecutionDate).HasColumnName("execution_date");
        entity.Property(e => e.Price)
            .HasPrecision(8, 2)
            .HasColumnName("price");
        entity.Property(e => e.PriceType)
            .HasMaxLength(255)
            .HasColumnName("price_type");
        entity.Property(e => e.Status)
            .HasMaxLength(255)
            .HasDefaultValueSql("'pending'")
            .HasColumnName("status");
        entity.Property(e => e.Type)
            .HasMaxLength(255)
            .HasColumnName("type");
        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.HasOne(d => d.Causer).WithMany(p => p.PriceAdjustments)
            .HasForeignKey(d => d.CauserId)
            .HasConstraintName("price_adjustments_causer_id_foreign");
    }
}