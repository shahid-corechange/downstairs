using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class ScheduleCleaningConfiguration : IEntityTypeConfiguration<ScheduleCleaning>
{
    public void Configure(EntityTypeBuilder<ScheduleCleaning> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint")
            .HasColumnName("id");

        entity.Property(e => e.CancelableId)
            .HasColumnType("bigint")
            .HasColumnName("cancelable_id");

        entity.Property(e => e.CancelableType)
            .HasColumnType("varchar(255)")
            .HasColumnName("cancelable_type");

        entity.Property(e => e.CanceledAt)
            .HasColumnType("timestamp")
            .HasColumnName("canceled_at");

        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");

        entity.Property(e => e.CustomerId)
            .HasColumnType("bigint")
            .HasColumnName("customer_id");

        entity.Property(e => e.DeletedAt)
            .HasColumnType("timestamp")
            .HasColumnName("deleted_at");

        entity.Property(e => e.EndAt)
            .HasColumnType("timestamp")
            .HasColumnName("end_at");

        entity.Property(e => e.IsFixed)
            .ValueGeneratedOnAdd()
            .HasColumnType("tinyint(1)")
            .HasColumnName("is_fixed")
            .HasDefaultValueSql("'0'");

        entity.Property(e => e.KeyInformation)
            .HasColumnType("text")
            .HasColumnName("key_information");

        entity.Property(e => e.LaundryOrderId)
            .HasColumnType("bigint")
            .HasColumnName("laundry_order_id");

        entity.Property(e => e.LaundryType)
            .HasColumnType("varchar(255)")
            .HasColumnName("laundry_type");

        entity.Property(e => e.Note)
            .HasColumnType("json")
            .HasColumnName("note");

        entity.Property(e => e.OriginalStartAt)
            .HasColumnType("timestamp")
            .HasColumnName("original_start_at");

        entity.Property(e => e.PropertyId)
            .HasColumnType("bigint")
            .HasColumnName("property_id");

        entity.Property(e => e.Quarters)
            .HasColumnType("smallint")
            .HasColumnName("quarters");

        entity.Property(e => e.StartAt)
            .HasColumnType("timestamp")
            .HasColumnName("start_at");

        entity.Property(e => e.Status)
            .ValueGeneratedOnAdd()
            .HasColumnType("varchar(255)")
            .HasColumnName("status")
            .HasDefaultValueSql("'booked'");

        entity.Property(e => e.SubscriptionId)
            .HasColumnType("bigint")
            .HasColumnName("subscription_id");

        entity.Property(e => e.TeamId)
            .HasColumnType("bigint")
            .HasColumnName("team_id");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => new { e.CancelableType, e.CancelableId }, "schedule_cleanings_cancelable_index");

        entity.HasIndex(e => e.CustomerId, "schedule_cleanings_customer_id_foreign");

        entity.HasIndex(e => e.EndAt, "schedule_cleanings_end_at_index");

        entity.HasIndex(e => e.LaundryOrderId, "schedule_cleanings_laundry_order_id_foreign");

        entity.HasIndex(e => e.LaundryType, "schedule_cleanings_laundry_type_index");

        entity.HasIndex(e => e.PropertyId, "schedule_cleanings_property_id_foreign");

        entity.HasIndex(e => e.StartAt, "schedule_cleanings_start_at_index");

        entity.HasIndex(e => e.Status, "schedule_cleanings_status_index");

        entity.HasIndex(e => e.SubscriptionId, "schedule_cleanings_subscription_id_foreign");

        entity.HasIndex(e => e.TeamId, "schedule_cleanings_team_id_foreign");

        entity.ToTable("schedule_cleanings").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.Customer)
            .WithMany(p => p.ScheduleCleanings)
            .HasForeignKey(d => d.CustomerId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("schedule_cleanings_customer_id_foreign");

        entity.HasOne(d => d.LaundryOrder)
            .WithMany(p => p.ScheduleCleanings)
            .HasForeignKey(d => d.LaundryOrderId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("schedule_cleanings_laundry_order_id_foreign");

        entity.HasOne(d => d.Property)
            .WithMany(p => p.ScheduleCleanings)
            .HasForeignKey(d => d.PropertyId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("schedule_cleanings_property_id_foreign");

        entity.HasOne(d => d.Subscription)
            .WithMany(p => p.ScheduleCleanings)
            .HasForeignKey(d => d.SubscriptionId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("schedule_cleanings_subscription_id_foreign");

        entity.HasOne(d => d.Team)
            .WithMany(p => p.ScheduleCleanings)
            .HasForeignKey(d => d.TeamId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("schedule_cleanings_team_id_foreign");
    }
}