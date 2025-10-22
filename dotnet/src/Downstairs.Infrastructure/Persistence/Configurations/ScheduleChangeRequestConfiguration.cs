using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class ScheduleChangeRequestConfiguration : IEntityTypeConfiguration<ScheduleChangeRequest>
{
    public void Configure(EntityTypeBuilder<ScheduleChangeRequest> entity)
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

        entity.Property(e => e.DeletedAt)
            .HasColumnType("timestamp")
            .HasColumnName("deleted_at");

        entity.Property(e => e.EndAtChanged)
            .HasColumnType("timestamp")
            .HasColumnName("end_at_changed");

        entity.Property(e => e.OriginalEndAt)
            .HasColumnType("timestamp")
            .HasColumnName("original_end_at");

        entity.Property(e => e.OriginalStartAt)
            .HasColumnType("timestamp")
            .HasColumnName("original_start_at");

        entity.Property(e => e.ScheduleId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("schedule_id");

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

        entity.HasIndex(e => e.CauserId, "schedule_change_requests_causer_id_foreign");

        entity.HasIndex(e => e.ScheduleId, "schedule_change_requests_schedule_id_foreign");

        entity.ToTable("schedule_change_requests").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.Causer)
            .WithMany(p => p.ScheduleChangeRequests)
            .HasForeignKey(d => d.CauserId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("schedule_change_requests_causer_id_foreign");

        entity.HasOne(d => d.Schedule)
            .WithMany(p => p.ScheduleChangeRequests)
            .HasForeignKey(d => d.ScheduleId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("schedule_change_requests_schedule_id_foreign");
    }
}