using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class FixedPriceRowConfiguration : IEntityTypeConfiguration<FixedPriceRow>
{
    public void Configure(EntityTypeBuilder<FixedPriceRow> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("fixed_price_rows")
            .UseCollation("utf8mb4_unicode_ci");

        entity.HasIndex(e => e.FixedPriceId, "fixed_price_rows_fixed_price_id_foreign");

        entity.HasIndex(e => e.Type, "fixed_price_rows_type_index");

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");
        entity.Property(e => e.FixedPriceId).HasColumnName("fixed_price_id");
        entity.Property(e => e.HasRut).HasColumnName("has_rut");
        entity.Property(e => e.Price)
            .HasColumnType("decimal(8,2) unsigned")
            .HasColumnName("price");
        entity.Property(e => e.Quantity).HasColumnName("quantity");
        entity.Property(e => e.Type).HasColumnName("type");
        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");
        entity.Property(e => e.VatGroup)
            .HasDefaultValueSql("'25'")
            .HasColumnName("vat_group");

        entity.HasOne(d => d.FixedPrice).WithMany(p => p.FixedPriceRows)
            .HasForeignKey(d => d.FixedPriceId)
            .HasConstraintName("fixed_price_rows_fixed_price_id_foreign");
    }
}

