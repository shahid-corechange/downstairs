using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class UnassignSubscriptionConfiguration : IEntityTypeConfiguration<UnassignSubscription>
{
    public void Configure(EntityTypeBuilder<UnassignSubscription> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("unassign_subscriptions")
            .UseCollation("utf8mb4_unicode_ci");

        entity.HasIndex(e => e.CustomerId, "unassign_subscriptions_customer_id_foreign");

        entity.HasIndex(e => e.PropertyId, "unassign_subscriptions_property_id_foreign");

        entity.HasIndex(e => e.ServiceId, "unassign_subscriptions_service_id_foreign");

        entity.HasIndex(e => e.UserId, "unassign_subscriptions_user_id_foreign");

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");
        entity.Property(e => e.CustomerId).HasColumnName("customer_id");
        entity.Property(e => e.Description)
            .HasColumnType("text")
            .HasColumnName("description");
        entity.Property(e => e.EndAt).HasColumnName("end_at");
        entity.Property(e => e.FixedPrice)
            .HasColumnType("decimal(8,2) unsigned")
            .HasColumnName("fixed_price");
        entity.Property(e => e.Frequency).HasColumnName("frequency");
        entity.Property(e => e.IsFixed).HasColumnName("is_fixed");
        entity.Property(e => e.ProductIds)
            .HasColumnType("json")
            .HasColumnName("product_ids");
        entity.Property(e => e.PropertyId).HasColumnName("property_id");
        entity.Property(e => e.Quarters).HasColumnName("quarters");
        entity.Property(e => e.RefillSequence)
            .HasDefaultValueSql("'12'")
            .HasColumnName("refill_sequence");
        entity.Property(e => e.ServiceId).HasColumnName("service_id");
        entity.Property(e => e.StartAt).HasColumnName("start_at");
        entity.Property(e => e.StartTimeAt)
            .HasColumnType("time")
            .HasColumnName("start_time_at");
        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");
        entity.Property(e => e.UserId).HasColumnName("user_id");

        entity.HasOne(d => d.Customer).WithMany(p => p.UnassignSubscriptions)
            .HasForeignKey(d => d.CustomerId)
            .HasConstraintName("unassign_subscriptions_customer_id_foreign");

        entity.HasOne(d => d.Property).WithMany(p => p.UnassignSubscriptions)
            .HasForeignKey(d => d.PropertyId)
            .HasConstraintName("unassign_subscriptions_property_id_foreign");

        entity.HasOne(d => d.Service).WithMany(p => p.UnassignSubscriptions)
            .HasForeignKey(d => d.ServiceId)
            .HasConstraintName("unassign_subscriptions_service_id_foreign");

        entity.HasOne(d => d.User).WithMany(p => p.UnassignSubscriptions)
            .HasForeignKey(d => d.UserId)
            .HasConstraintName("unassign_subscriptions_user_id_foreign");
    }
}

