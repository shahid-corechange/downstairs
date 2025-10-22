using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class SubscriptionStaffDetailConfiguration : IEntityTypeConfiguration<SubscriptionStaffDetail>
{
    public void Configure(EntityTypeBuilder<SubscriptionStaffDetail> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint unsigned")
            .HasColumnName("id");

        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");

        entity.Property(e => e.DeletedAt)
            .HasColumnType("timestamp")
            .HasColumnName("deleted_at");

        entity.Property(e => e.IsActive)
            .IsRequired()
            .ValueGeneratedOnAdd()
            .HasColumnType("tinyint(1)")
            .HasColumnName("is_active")
            .HasDefaultValueSql("'1'");

        entity.Property(e => e.Quarters)
            .HasColumnType("int")
            .HasColumnName("quarters");

        entity.Property(e => e.SubscriptionId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("subscription_id");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.Property(e => e.UserId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("user_id");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.SubscriptionId, "subscription_staff_details_subscription_id_foreign");

        entity.HasIndex(e => e.UserId, "subscription_staff_details_user_id_foreign");

        entity.ToTable("subscription_staff_details").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.Subscription)
            .WithMany(p => p.SubscriptionStaffDetails)
            .HasForeignKey(d => d.SubscriptionId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("subscription_staff_details_subscription_id_foreign");

        entity.HasOne(d => d.User)
            .WithMany(p => p.SubscriptionStaffDetails)
            .HasForeignKey(d => d.UserId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("subscription_staff_details_user_id_foreign");
    }
}