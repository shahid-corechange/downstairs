using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class SubscriptionItemConfiguration : IEntityTypeConfiguration<SubscriptionItem>
{
    public void Configure(EntityTypeBuilder<SubscriptionItem> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint unsigned")
            .HasColumnName("id");

        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");

        entity.Property(e => e.ItemableId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("itemable_id");

        entity.Property(e => e.ItemableType)
            .IsRequired()
            .HasColumnType("varchar(255)")
            .HasColumnName("itemable_type");

        entity.Property(e => e.Quantity)
            .ValueGeneratedOnAdd()
            .HasColumnType("smallint unsigned")
            .HasColumnName("quantity")
            .HasDefaultValueSql("'1'");

        entity.Property(e => e.SubscriptionId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("subscription_id");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => new { e.ItemableType, e.ItemableId }, "subscription_items_itemable_type_itemable_id_index");

        entity.HasIndex(e => e.SubscriptionId, "subscription_items_subscription_id_foreign");

        entity.ToTable("subscription_items").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.Subscription)
            .WithMany(p => p.SubscriptionItems)
            .HasForeignKey(d => d.SubscriptionId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("subscription_items_subscription_id_foreign");
    }
}