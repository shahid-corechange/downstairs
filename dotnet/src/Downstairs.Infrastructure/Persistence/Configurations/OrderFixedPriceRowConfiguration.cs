using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class OrderFixedPriceRowConfiguration : IEntityTypeConfiguration<OrderFixedPriceRow>
{
    public void Configure(EntityTypeBuilder<OrderFixedPriceRow> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("order_fixed_price_rows")
            .HasCharSet("utf8mb3")
            .UseCollation("utf8mb3_general_ci");

        entity.HasIndex(e => e.OrderFixedPriceId, "order_fixed_price_rows_order_fixed_price_id_foreign");

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");
        entity.Property(e => e.Description)
            .HasMaxLength(255)
            .HasColumnName("description");
        entity.Property(e => e.HasRut).HasColumnName("has_rut");
        entity.Property(e => e.OrderFixedPriceId).HasColumnName("order_fixed_price_id");
        entity.Property(e => e.Price)
            .HasColumnType("decimal(8,2) unsigned")
            .HasColumnName("price");
        entity.Property(e => e.Quantity).HasColumnName("quantity");
        entity.Property(e => e.Type)
            .HasMaxLength(255)
            .HasColumnName("type");
        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");
        entity.Property(e => e.VatGroup)
            .HasDefaultValueSql("'25'")
            .HasColumnName("vat_group");

        entity.HasOne(d => d.OrderFixedPrice).WithMany(p => p.OrderFixedPriceRows)
            .HasForeignKey(d => d.OrderFixedPriceId)
            .HasConstraintName("order_fixed_price_rows_order_fixed_price_id_foreign");
    }
}

