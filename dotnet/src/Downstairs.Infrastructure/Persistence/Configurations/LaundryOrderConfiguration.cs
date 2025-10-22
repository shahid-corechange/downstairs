using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class LaundryOrderConfiguration : IEntityTypeConfiguration<LaundryOrder>
{
    public void Configure(EntityTypeBuilder<LaundryOrder> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint unsigned")
            .HasColumnName("id");

        entity.Property(e => e.CauserId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("causer_id");

        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");

        entity.Property(e => e.CustomerId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("customer_id");

        entity.Property(e => e.DeletedAt)
            .HasColumnType("timestamp")
            .HasColumnName("deleted_at");

        entity.Property(e => e.DeliveryPropertyId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("delivery_property_id");

        entity.Property(e => e.DeliveryTeamId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("delivery_team_id");

        entity.Property(e => e.DeliveryTime)
            .HasColumnType("time")
            .HasColumnName("delivery_time");

        entity.Property(e => e.LaundryPreferenceId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("laundry_preference_id");

        entity.Property(e => e.OrderedAt)
            .HasColumnType("timestamp")
            .HasColumnName("ordered_at");

        entity.Property(e => e.PaidAt)
            .HasColumnType("timestamp")
            .HasColumnName("paid_at");

        entity.Property(e => e.PaymentMethod)
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("payment_method");

        entity.Property(e => e.PickupPropertyId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("pickup_property_id");

        entity.Property(e => e.PickupTeamId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("pickup_team_id");

        entity.Property(e => e.PickupTime)
            .HasColumnType("time")
            .HasColumnName("pickup_time");

        entity.Property(e => e.Status)
            .IsRequired()
            .ValueGeneratedOnAdd()
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("status")
            .HasDefaultValueSql("'pending'");

        entity.Property(e => e.StoreId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("store_id");

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

        entity.HasIndex(e => e.StoreId, "laundry_orders_store_id_foreign");

        entity.HasIndex(e => e.UserId, "laundry_orders_user_id_foreign");
        
        entity.HasIndex(e => e.CauserId, "laundry_orders_causer_id_foreign");

        entity.HasIndex(e => e.LaundryPreferenceId, "laundry_orders_laundry_preference_id_foreign");

        entity.HasIndex(e => e.SubscriptionId, "laundry_orders_subscription_id_foreign");

        entity.HasIndex(e => e.CustomerId, "laundry_orders_customer_id_foreign");

        entity.HasIndex(e => e.PickupPropertyId, "laundry_orders_pickup_property_id_foreign");

        entity.HasIndex(e => e.PickupTeamId, "laundry_orders_pickup_team_id_foreign");

        entity.HasIndex(e => e.DeliveryPropertyId, "laundry_orders_delivery_property_id_foreign");

        entity.HasIndex(e => e.DeliveryTeamId, "laundry_orders_delivery_team_id_foreign");

        entity.ToTable("laundry_orders").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.Causer)
            .WithMany(p => p.LaundryOrderCausers)
            .HasForeignKey(d => d.CauserId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("laundry_orders_causer_id_foreign");

        entity.HasOne(d => d.Customer)
            .WithMany(p => p.LaundryOrders)
            .HasForeignKey(d => d.CustomerId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("laundry_orders_customer_id_foreign");

        entity.HasOne(d => d.DeliveryProperty)
            .WithMany(p => p.LaundryOrderDeliveryProperties)
            .HasForeignKey(d => d.DeliveryPropertyId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("laundry_orders_delivery_property_id_foreign");

        entity.HasOne(d => d.DeliveryTeam)
            .WithMany(p => p.LaundryOrderDeliveryTeams)
            .HasForeignKey(d => d.DeliveryTeamId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("laundry_orders_delivery_team_id_foreign");

        entity.HasOne(d => d.LaundryPreference)
            .WithMany(p => p.LaundryOrders)
            .HasForeignKey(d => d.LaundryPreferenceId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("laundry_orders_laundry_preference_id_foreign");

        entity.HasOne(d => d.PickupProperty)
            .WithMany(p => p.LaundryOrderPickupProperties)
            .HasForeignKey(d => d.PickupPropertyId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("laundry_orders_pickup_property_id_foreign");

        entity.HasOne(d => d.PickupTeam)
            .WithMany(p => p.LaundryOrderPickupTeams)
            .HasForeignKey(d => d.PickupTeamId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("laundry_orders_pickup_team_id_foreign");

        entity.HasOne(d => d.Store)
            .WithMany(p => p.LaundryOrders)
            .HasForeignKey(d => d.StoreId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("laundry_orders_store_id_foreign");

        entity.HasOne(d => d.Subscription)
            .WithMany(p => p.LaundryOrders)
            .HasForeignKey(d => d.SubscriptionId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("laundry_orders_subscription_id_foreign");

        entity.HasOne(d => d.User)
            .WithMany(p => p.LaundryOrderUsers)
            .HasForeignKey(d => d.UserId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("laundry_orders_user_id_foreign");
    }
}