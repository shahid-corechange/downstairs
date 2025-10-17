using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class SubscriptionConfiguration : IEntityTypeConfiguration<Subscription>
{
    public void Configure(EntityTypeBuilder<Subscription> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("subscriptions")
            .UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasIndex(e => e.CustomerId, "subscriptions_customer_id_foreign");

        entity.HasIndex(e => e.FixedPriceId, "subscriptions_fixed_price_id_foreign");

        entity.HasIndex(e => e.PropertyId, "subscriptions_property_id_foreign");

        entity.HasIndex(e => e.ServiceId, "subscriptions_service_id_foreign");

        entity.HasIndex(e => e.TeamId, "subscriptions_team_id_foreign");

        entity.HasIndex(e => e.UserId, "subscriptions_user_id_foreign");

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");
        entity.Property(e => e.CustomerId).HasColumnName("customer_id");
        entity.Property(e => e.DeletedAt)
            .HasColumnType("timestamp")
            .HasColumnName("deleted_at");
        entity.Property(e => e.Description)
            .HasColumnType("text")
            .HasColumnName("description");
        entity.Property(e => e.EndAt).HasColumnName("end_at");
        entity.Property(e => e.EndTimeAt)
            .HasColumnType("time")
            .HasColumnName("end_time_at");
        entity.Property(e => e.FixedPriceId).HasColumnName("fixed_price_id");
        entity.Property(e => e.Frequency).HasColumnName("frequency");
        entity.Property(e => e.IsFixed).HasColumnName("is_fixed");
        entity.Property(e => e.IsPaused).HasColumnName("is_paused");
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
        entity.Property(e => e.TeamId).HasColumnName("team_id");
        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");
        entity.Property(e => e.UserId).HasColumnName("user_id");

        entity.HasOne(d => d.Customer).WithMany(p => p.Subscriptions)
            .HasForeignKey(d => d.CustomerId)
            .HasConstraintName("subscriptions_customer_id_foreign");

        entity.HasOne(d => d.FixedPrice).WithMany(p => p.Subscriptions)
            .HasForeignKey(d => d.FixedPriceId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("subscriptions_fixed_price_id_foreign");

        entity.HasOne(d => d.Property).WithMany(p => p.Subscriptions)
            .HasForeignKey(d => d.PropertyId)
            .HasConstraintName("subscriptions_property_id_foreign");

        entity.HasOne(d => d.Service).WithMany(p => p.Subscriptions)
            .HasForeignKey(d => d.ServiceId)
            .HasConstraintName("subscriptions_service_id_foreign");

        entity.HasOne(d => d.Team).WithMany(p => p.Subscriptions)
            .HasForeignKey(d => d.TeamId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("subscriptions_team_id_foreign");

        entity.HasOne(d => d.User).WithMany(p => p.Subscriptions)
            .HasForeignKey(d => d.UserId)
            .HasConstraintName("subscriptions_user_id_foreign");
    }
}