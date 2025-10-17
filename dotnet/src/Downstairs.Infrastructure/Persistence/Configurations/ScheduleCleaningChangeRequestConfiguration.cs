using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class ScheduleCleaningChangeRequestConfiguration : IEntityTypeConfiguration<ScheduleCleaningChangeRequest>
{
    public void Configure(EntityTypeBuilder<ScheduleCleaningChangeRequest> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("schedule_cleaning_change_requests")
            .UseCollation("utf8mb4_unicode_ci");

        entity.HasIndex(e => e.CauserId, "schedule_cleaning_change_requests_causer_id_foreign");

        entity.HasIndex(e => e.ScheduleCleaningId, "schedule_cleaning_change_requests_schedule_cleaning_id_foreign");

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.CauserId).HasColumnName("causer_id");
        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");
        entity.Property(e => e.DeletedAt)
            .HasColumnType("timestamp")
            .HasColumnName("deleted_at");
        entity.Property(e => e.EndAtChanged)
            .HasColumnType("timestamp")
            .HasColumnName("end_at_changed");
        entity.Property(e => e.OriginalEndAt)
            .HasMaxLength(255)
            .HasColumnName("original_end_at");
        entity.Property(e => e.OriginalStartAt)
            .HasMaxLength(255)
            .HasColumnName("original_start_at");
        entity.Property(e => e.ScheduleCleaningId).HasColumnName("schedule_cleaning_id");
        entity.Property(e => e.StartAtChanged)
            .HasColumnType("timestamp")
            .HasColumnName("start_at_changed");
        entity.Property(e => e.Status)
            .HasMaxLength(255)
            .HasDefaultValueSql("'pending'")
            .HasColumnName("status");
        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.HasOne(d => d.Causer).WithMany(p => p.ScheduleCleaningChangeRequests)
            .HasForeignKey(d => d.CauserId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("schedule_cleaning_change_requests_causer_id_foreign");

        entity.HasOne(d => d.ScheduleCleaning).WithMany(p => p.ScheduleCleaningChangeRequests)
            .HasForeignKey(d => d.ScheduleCleaningId)
            .HasConstraintName("schedule_cleaning_change_requests_schedule_cleaning_id_foreign");
    }
}

