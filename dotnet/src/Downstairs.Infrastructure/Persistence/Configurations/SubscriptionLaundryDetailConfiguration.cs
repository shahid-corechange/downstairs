using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class SubscriptionLaundryDetailConfiguration : IEntityTypeConfiguration<SubscriptionLaundryDetail>
{
    public void Configure(EntityTypeBuilder<SubscriptionLaundryDetail> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint")
            .HasColumnName("id");

        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");

        entity.Property(e => e.DeliveryPropertyId)
            .HasColumnType("bigint")
            .HasColumnName("delivery_property_id");

        entity.Property(e => e.DeliveryTeamId)
            .HasColumnType("bigint")
            .HasColumnName("delivery_team_id");

        entity.Property(e => e.DeliveryTime)
            .HasColumnType("time")
            .HasColumnName("delivery_time");

        entity.Property(e => e.LaundryPreferenceId)
            .HasColumnType("bigint")
            .HasColumnName("laundry_preference_id");

        entity.Property(e => e.PickupPropertyId)
            .HasColumnType("bigint")
            .HasColumnName("pickup_property_id");

        entity.Property(e => e.PickupTeamId)
            .HasColumnType("bigint")
            .HasColumnName("pickup_team_id");

        entity.Property(e => e.PickupTime)
            .HasColumnType("time")
            .HasColumnName("pickup_time");

        entity.Property(e => e.StoreId)
            .HasColumnType("bigint")
            .HasColumnName("store_id");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.DeliveryPropertyId, "subscription_laundry_details_delivery_property_id_foreign");

        entity.HasIndex(e => e.DeliveryTeamId, "subscription_laundry_details_delivery_team_id_foreign");

        entity.HasIndex(e => e.LaundryPreferenceId, "subscription_laundry_details_laundry_preference_id_foreign");

        entity.HasIndex(e => e.PickupPropertyId, "subscription_laundry_details_pickup_property_id_foreign");

        entity.HasIndex(e => e.PickupTeamId, "subscription_laundry_details_pickup_team_id_foreign");

        entity.HasIndex(e => e.StoreId, "subscription_laundry_details_store_id_foreign");

        entity.ToTable("subscription_laundry_details").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.DeliveryProperty)
            .WithMany(p => p.SubscriptionLaundryDetailDeliveryProperties)
            .HasForeignKey(d => d.DeliveryPropertyId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("subscription_laundry_details_delivery_property_id_foreign");

        entity.HasOne(d => d.DeliveryTeam)
            .WithMany(p => p.SubscriptionLaundryDetailDeliveryTeams)
            .HasForeignKey(d => d.DeliveryTeamId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("subscription_laundry_details_delivery_team_id_foreign");

        entity.HasOne(d => d.LaundryPreference)
            .WithMany(p => p.SubscriptionLaundryDetails)
            .HasForeignKey(d => d.LaundryPreferenceId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("subscription_laundry_details_laundry_preference_id_foreign");

        entity.HasOne(d => d.PickupProperty)
            .WithMany(p => p.SubscriptionLaundryDetailPickupProperties)
            .HasForeignKey(d => d.PickupPropertyId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("subscription_laundry_details_pickup_property_id_foreign");

        entity.HasOne(d => d.PickupTeam)
            .WithMany(p => p.SubscriptionLaundryDetailPickupTeams)
            .HasForeignKey(d => d.PickupTeamId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("subscription_laundry_details_pickup_team_id_foreign");

        entity.HasOne(d => d.Store)
            .WithMany(p => p.SubscriptionLaundryDetails)
            .HasForeignKey(d => d.StoreId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("subscription_laundry_details_store_id_foreign");
    }
}