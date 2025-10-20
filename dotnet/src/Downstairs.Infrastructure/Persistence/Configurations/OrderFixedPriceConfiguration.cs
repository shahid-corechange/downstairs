using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class OrderFixedPriceConfiguration : IEntityTypeConfiguration<OrderFixedPrice>
{
    public void Configure(EntityTypeBuilder<OrderFixedPrice> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint")
            .HasColumnName("id");

        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");

        entity.Property(e => e.DeletedAt)
            .HasColumnType("timestamp")
            .HasColumnName("deleted_at");

        entity.Property(e => e.FixedPriceId)
            .HasColumnType("bigint")
            .HasColumnName("fixed_price_id");

        entity.Property(e => e.IsPerOrder)
            .HasColumnType("tinyint(1)")
            .HasColumnName("is_per_order");

        entity.Property(e => e.Type)
            .IsRequired()
            .ValueGeneratedOnAdd()
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("type")
            .HasDefaultValueSql("'cleaning'");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.FixedPriceId, "order_fixed_prices_fixed_price_id_foreign");

        entity.ToTable("order_fixed_prices").HasCharSet(DatabaseConstants.CharSets.Utf8mb4).UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.FixedPrice)
            .WithMany(p => p.OrderFixedPrices)
            .HasForeignKey(d => d.FixedPriceId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("order_fixed_prices_fixed_price_id_foreign");
    }
}