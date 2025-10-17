using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class SubscriptionStaffDetailConfiguration : IEntityTypeConfiguration<SubscriptionStaffDetail>
{
    public void Configure(EntityTypeBuilder<SubscriptionStaffDetail> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("subscription_staff_details")
            .UseCollation("utf8mb4_unicode_ci");

        entity.HasIndex(e => e.SubscriptionId, "subscription_staff_details_subscription_id_foreign");

        entity.HasIndex(e => e.UserId, "subscription_staff_details_user_id_foreign");

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");
        entity.Property(e => e.DeletedAt)
            .HasColumnType("timestamp")
            .HasColumnName("deleted_at");
        entity.Property(e => e.IsActive)
            .IsRequired()
            .HasDefaultValueSql("'1'")
            .HasColumnName("is_active");
        entity.Property(e => e.Quarters).HasColumnName("quarters");
        entity.Property(e => e.SubscriptionId).HasColumnName("subscription_id");
        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");
        entity.Property(e => e.UserId).HasColumnName("user_id");

        entity.HasOne(d => d.Subscription).WithMany(p => p.SubscriptionStaffDetails)
            .HasForeignKey(d => d.SubscriptionId)
            .HasConstraintName("subscription_staff_details_subscription_id_foreign");

        entity.HasOne(d => d.User).WithMany(p => p.SubscriptionStaffDetails)
            .HasForeignKey(d => d.UserId)
            .HasConstraintName("subscription_staff_details_user_id_foreign");
    }
}

