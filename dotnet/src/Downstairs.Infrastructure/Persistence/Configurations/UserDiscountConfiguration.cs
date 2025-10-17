using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class UserDiscountConfiguration : IEntityTypeConfiguration<UserDiscount>
{
    public void Configure(EntityTypeBuilder<UserDiscount> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("user_discounts")
            .UseCollation(DatabaseConstants.Collations.Unicode);

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");
        entity.Property(e => e.DeletedAt)
            .HasColumnType("timestamp")
            .HasColumnName("deleted_at");
        entity.Property(e => e.Discount).HasColumnName("discount");
        entity.Property(e => e.ProductGroup).HasColumnName("product_group");
        entity.Property(e => e.ProductId).HasColumnName("product_id");
        entity.Property(e => e.Repeatable)
            .HasDefaultValueSql("'1'")
            .HasColumnName("repeatable");
        entity.Property(e => e.Status)
            .HasMaxLength(255)
            .HasDefaultValueSql("'active'")
            .HasColumnName("status");
        entity.Property(e => e.Type)
            .HasMaxLength(255)
            .HasDefaultValueSql("'cleaning'")
            .HasColumnName("type");
        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");
        entity.Property(e => e.UserId).HasColumnName("user_id");
        entity.Property(e => e.ValidFromAt)
            .HasColumnType("datetime")
            .HasColumnName("valid_from_at");
        entity.Property(e => e.ValidToAt)
            .HasColumnType("datetime")
            .HasColumnName("valid_to_at");
    }
}