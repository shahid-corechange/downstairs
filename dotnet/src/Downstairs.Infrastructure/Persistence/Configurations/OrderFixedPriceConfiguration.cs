using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class OrderFixedPriceConfiguration : IEntityTypeConfiguration<OrderFixedPrice>
{
    public void Configure(EntityTypeBuilder<OrderFixedPrice> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("order_fixed_prices")
            .HasCharSet(DatabaseConstants.CharSets.Utf8mb4)
            .UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasIndex(e => e.FixedPriceId, "order_fixed_prices_fixed_price_id_foreign");

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");
        entity.Property(e => e.DeletedAt)
            .HasColumnType("timestamp")
            .HasColumnName("deleted_at");
        entity.Property(e => e.FixedPriceId).HasColumnName("fixed_price_id");
        entity.Property(e => e.IsPerOrder).HasColumnName("is_per_order");
        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.HasOne(d => d.FixedPrice).WithMany(p => p.OrderFixedPrices)
            .HasForeignKey(d => d.FixedPriceId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("order_fixed_prices_fixed_price_id_foreign");
    }
}