using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class ScheduleConfiguration : IEntityTypeConfiguration<Schedule>
{
    public void Configure(EntityTypeBuilder<Schedule> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint unsigned")
            .HasColumnName("id");

        entity.Property(e => e.CancelableId)
            .HasColumnType("bigint unsigned")
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
            .HasColumnType("bigint unsigned")
            .HasColumnName("customer_id");

        entity.Property(e => e.DeletedAt)
            .HasColumnType("timestamp")
            .HasColumnName("deleted_at");

        entity.Property(e => e.EndAt)
            .HasColumnType("timestamp")
            .HasColumnName("end_at");

        entity.Property(e => e.IsFixed)
            .HasColumnType("tinyint(1)")
            .HasColumnName("is_fixed")
            .HasDefaultValueSql("'0'");

        entity.Property(e => e.KeyInformation)
            .HasColumnType("text")
            .HasColumnName("key_information");

        entity.Property(e => e.Note)
            .HasColumnType("text")
            .HasColumnName("note");

        entity.Property(e => e.OriginalStartAt)
            .HasColumnType("timestamp")
            .HasColumnName("original_start_at");

        entity.Property(e => e.PropertyId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("property_id");

        entity.Property(e => e.Quarters)
            .HasColumnType("smallint")
            .HasColumnName("quarters");

        entity.Property(e => e.ScheduleableId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("scheduleable_id");

        entity.Property(e => e.ScheduleableType)
            .IsRequired()
            .HasColumnType("varchar(255)")
            .HasColumnName("scheduleable_type");

        entity.Property(e => e.ServiceId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("service_id");

        entity.Property(e => e.StartAt)
            .HasColumnType("timestamp")
            .HasColumnName("start_at");

        entity.Property(e => e.Status)
            .IsRequired()
            .ValueGeneratedOnAdd()
            .HasColumnType("varchar(255)")
            .HasColumnName("status")
            .HasDefaultValueSql("'booked'");

        entity.Property(e => e.SubscriptionId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("subscription_id");

        entity.Property(e => e.TeamId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("team_id");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.Property(e => e.UserId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("user_id");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.CancelableId, "schedules_cancelable_id_index");

        entity.HasIndex(e => e.CancelableType, "schedules_cancelable_type_index");

        entity.HasIndex(e => e.CustomerId, "schedules_customer_id_foreign");

        entity.HasIndex(e => e.OriginalStartAt, "schedules_original_start_at_index");

        entity.HasIndex(e => e.PropertyId, "schedules_property_id_foreign");

        entity.HasIndex(e => new { e.ScheduleableType, e.ScheduleableId }, "schedules_scheduleable_type_scheduleable_id_index");

        entity.HasIndex(e => e.ServiceId, "schedules_service_id_foreign");

        entity.HasIndex(e => e.StartAt, "schedules_start_at_index");

        entity.HasIndex(e => e.Status, "schedules_status_index");

        entity.HasIndex(e => e.SubscriptionId, "schedules_subscription_id_foreign");

        entity.HasIndex(e => e.TeamId, "schedules_team_id_foreign");

        entity.HasIndex(e => e.UserId, "schedules_user_id_foreign");

        entity.ToTable("schedules").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.Customer)
            .WithMany(p => p.Schedules)
            .HasForeignKey(d => d.CustomerId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("schedules_customer_id_foreign");

        entity.HasOne(d => d.Property)
            .WithMany(p => p.Schedules)
            .HasForeignKey(d => d.PropertyId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("schedules_property_id_foreign");

        entity.HasOne(d => d.Service)
            .WithMany(p => p.Schedules)
            .HasForeignKey(d => d.ServiceId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("schedules_service_id_foreign");

        entity.HasOne(d => d.Subscription)
            .WithMany(p => p.Schedules)
            .HasForeignKey(d => d.SubscriptionId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("schedules_subscription_id_foreign");

        entity.HasOne(d => d.Team)
            .WithMany(p => p.Schedules)
            .HasForeignKey(d => d.TeamId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("schedules_team_id_foreign");

        entity.HasOne(d => d.User)
            .WithMany(p => p.Schedules)
            .HasForeignKey(d => d.UserId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("schedules_user_id_foreign");
    }
}