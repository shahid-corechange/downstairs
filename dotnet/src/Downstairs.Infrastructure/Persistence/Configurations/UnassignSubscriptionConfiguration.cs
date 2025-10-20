using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class UnassignSubscriptionConfiguration : IEntityTypeConfiguration<UnassignSubscription>
{
    public void Configure(EntityTypeBuilder<UnassignSubscription> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint")
            .HasColumnName("id");

        entity.Property(e => e.AddonIds)
            .HasColumnType("json")
            .HasColumnName("addon_ids");

        entity.Property(e => e.CleaningDetail)
            .HasColumnType("json")
            .HasColumnName("cleaning_detail");

        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");

        entity.Property(e => e.CustomerId)
            .HasColumnType("bigint")
            .HasColumnName("customer_id");

        entity.Property(e => e.Description)
            .HasColumnType("text")
            .HasColumnName("description");

        entity.Property(e => e.EndAt)
            .HasColumnType("date")
            .HasColumnName("end_at");

        entity.Property(e => e.FixedPrice)
            .HasColumnType("decimal(8,2)")
            .HasColumnName("fixed_price");

        entity.Property(e => e.Frequency)
            .HasColumnType("smallint")
            .HasColumnName("frequency");

        entity.Property(e => e.IsFixed)
            .HasColumnType("tinyint(1)")
            .HasColumnName("is_fixed");

        entity.Property(e => e.LaundryDetail)
            .HasColumnType("json")
            .HasColumnName("laundry_detail");

        entity.Property(e => e.ProductCarts)
            .HasColumnType("json")
            .HasColumnName("product_carts");

        entity.Property(e => e.ServiceId)
            .HasColumnType("bigint")
            .HasColumnName("service_id");

        entity.Property(e => e.StartAt)
            .HasColumnType("date")
            .HasColumnName("start_at");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.Property(e => e.UserId)
            .HasColumnType("bigint")
            .HasColumnName("user_id");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.CustomerId, "unassign_subscriptions_customer_id_foreign");

        entity.HasIndex(e => e.ServiceId, "unassign_subscriptions_service_id_foreign");

        entity.HasIndex(e => e.UserId, "unassign_subscriptions_user_id_foreign");

        entity.ToTable("unassign_subscriptions").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.Customer)
            .WithMany(p => p.UnassignSubscriptions)
            .HasForeignKey(d => d.CustomerId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("unassign_subscriptions_customer_id_foreign");

        entity.HasOne(d => d.Service)
            .WithMany(p => p.UnassignSubscriptions)
            .HasForeignKey(d => d.ServiceId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("unassign_subscriptions_service_id_foreign");

        entity.HasOne(d => d.User)
            .WithMany(p => p.UnassignSubscriptions)
            .HasForeignKey(d => d.UserId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("unassign_subscriptions_user_id_foreign");
    }
}