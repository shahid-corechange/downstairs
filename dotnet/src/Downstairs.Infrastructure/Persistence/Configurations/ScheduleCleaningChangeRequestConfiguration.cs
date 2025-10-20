using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class ScheduleCleaningChangeRequestConfiguration : IEntityTypeConfiguration<ScheduleCleaningChangeRequest>
{
    public void Configure(EntityTypeBuilder<ScheduleCleaningChangeRequest> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint")
            .HasColumnName("id");

        entity.Property(e => e.CauserId)
            .HasColumnType("bigint")
            .HasColumnName("causer_id");

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
            .HasColumnType("varchar(255)")
            .HasColumnName("original_end_at");

        entity.Property(e => e.OriginalStartAt)
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("original_start_at");

        entity.Property(e => e.ScheduleCleaningId)
            .HasColumnType("bigint")
            .HasColumnName("schedule_cleaning_id");

        entity.Property(e => e.StartAtChanged)
            .HasColumnType("timestamp")
            .HasColumnName("start_at_changed");

        entity.Property(e => e.Status)
            .IsRequired()
            .ValueGeneratedOnAdd()
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("status")
            .HasDefaultValueSql("'pending'");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.CauserId, "schedule_cleaning_change_requests_causer_id_foreign");

        entity.HasIndex(e => e.ScheduleCleaningId, "schedule_cleaning_change_requests_schedule_cleaning_id_foreign");

        entity.ToTable("schedule_cleaning_change_requests").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.Causer)
            .WithMany(p => p.ScheduleCleaningChangeRequests)
            .HasForeignKey(d => d.CauserId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("schedule_cleaning_change_requests_causer_id_foreign");

        entity.HasOne(d => d.ScheduleCleaning)
            .WithMany(p => p.ScheduleCleaningChangeRequests)
            .HasForeignKey(d => d.ScheduleCleaningId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("schedule_cleaning_change_requests_schedule_cleaning_id_foreign");
    }
}