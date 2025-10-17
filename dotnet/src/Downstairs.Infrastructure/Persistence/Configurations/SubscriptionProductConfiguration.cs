using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class SubscriptionProductConfiguration : IEntityTypeConfiguration<SubscriptionProduct>
{
    public void Configure(EntityTypeBuilder<SubscriptionProduct> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("subscription_product")
            .UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasIndex(e => e.ProductId, "subscription_product_product_id_foreign");

        entity.HasIndex(e => e.SubscriptionId, "subscription_product_subscription_id_foreign");

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");
        entity.Property(e => e.ProductId).HasColumnName("product_id");
        entity.Property(e => e.Quantity).HasColumnName("quantity");
        entity.Property(e => e.SubscriptionId).HasColumnName("subscription_id");
        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.HasOne(d => d.Product).WithMany(p => p.SubscriptionProducts)
            .HasForeignKey(d => d.ProductId)
            .HasConstraintName("subscription_product_product_id_foreign");

        entity.HasOne(d => d.Subscription).WithMany(p => p.SubscriptionProducts)
            .HasForeignKey(d => d.SubscriptionId)
            .HasConstraintName("subscription_product_subscription_id_foreign");
    }
}