using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class SubscriptionConfiguration : IEntityTypeConfiguration<Subscription>
{
    public void Configure(EntityTypeBuilder<Subscription> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint unsigned")
            .HasColumnName("id");

        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");

        entity.Property(e => e.FixedPriceId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("fixed_price_id");

        entity.Property(e => e.Frequency)
            .HasColumnType("smallint")
            .HasColumnName("frequency");

        entity.Property(e => e.IsFixed)
            .HasColumnType("tinyint(1)")
            .HasColumnName("is_fixed")
            .HasDefaultValueSql("'0'");

        entity.Property(e => e.IsPaused)
            .HasColumnType("tinyint(1)")
            .HasColumnName("is_paused")
            .HasDefaultValueSql("'0'");

        entity.Property(e => e.ServiceId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("service_id");

        entity.Property(e => e.StartAt)
            .HasColumnType("date")
            .HasColumnName("start_at");

        entity.Property(e => e.SubscribableId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("subscribable_id");

        entity.Property(e => e.SubscribableType)
            .IsRequired()
            .HasColumnType("varchar(255)")
            .HasColumnName("subscribable_type");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.Property(e => e.UserId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("user_id");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.FixedPriceId, "subscriptions_fixed_price_id_foreign");

        entity.HasIndex(e => e.ServiceId, "subscriptions_service_id_foreign");

        entity.HasIndex(e => new { e.SubscribableId, e.SubscribableType }, "subscriptions_subscribable_id_subscribable_type_index");

        entity.HasIndex(e => e.UserId, "subscriptions_user_id_foreign");

        entity.ToTable("subscriptions").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.FixedPrice)
            .WithMany(p => p.Subscriptions)
            .HasForeignKey(d => d.FixedPriceId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("subscriptions_fixed_price_id_foreign")
            .HasAnnotation("Relational:OnUpdate", "RESTRICT");

        entity.HasOne(d => d.Service)
            .WithMany(p => p.Subscriptions)
            .HasForeignKey(d => d.ServiceId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("subscriptions_service_id_foreign");

        entity.HasOne(d => d.User)
            .WithMany(p => p.Subscriptions)
            .HasForeignKey(d => d.UserId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("subscriptions_user_id_foreign");
    }
}