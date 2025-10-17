using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class FixedPriceConfiguration : IEntityTypeConfiguration<FixedPrice>
{
    public void Configure(EntityTypeBuilder<FixedPrice> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("fixed_prices")
            .UseCollation("utf8mb4_unicode_ci");

        entity.HasIndex(e => e.CreatedAt, "fixed_prices_created_at_index");

        entity.HasIndex(e => e.UserId, "fixed_prices_user_id_foreign");

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");
        entity.Property(e => e.DeletedAt)
            .HasColumnType("timestamp")
            .HasColumnName("deleted_at");
        entity.Property(e => e.EndDate).HasColumnName("end_date");
        entity.Property(e => e.IsPerOrder).HasColumnName("is_per_order");
        entity.Property(e => e.StartDate).HasColumnName("start_date");
        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");
        entity.Property(e => e.UserId).HasColumnName("user_id");

        entity.HasOne(d => d.User).WithMany(p => p.FixedPrices)
            .HasForeignKey(d => d.UserId)
            .HasConstraintName("fixed_prices_user_id_foreign");
    }
}

