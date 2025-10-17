using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class TimeAdjustmentConfiguration : IEntityTypeConfiguration<TimeAdjustment>
{
    public void Configure(EntityTypeBuilder<TimeAdjustment> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("time_adjustments")
            .UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasIndex(e => e.CauserId, "time_adjustments_causer_id_foreign");

        entity.HasIndex(e => e.ScheduleEmployeeId, "time_adjustments_schedule_employee_id_foreign");

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.CauserId).HasColumnName("causer_id");
        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");
        entity.Property(e => e.Quarters).HasColumnName("quarters");
        entity.Property(e => e.Reason)
            .HasMaxLength(255)
            .HasColumnName("reason");
        entity.Property(e => e.ScheduleEmployeeId).HasColumnName("schedule_employee_id");
        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.HasOne(d => d.Causer).WithMany(p => p.TimeAdjustments)
            .HasForeignKey(d => d.CauserId)
            .HasConstraintName("time_adjustments_causer_id_foreign");

        entity.HasOne(d => d.ScheduleEmployee).WithMany(p => p.TimeAdjustments)
            .HasForeignKey(d => d.ScheduleEmployeeId)
            .HasConstraintName("time_adjustments_schedule_employee_id_foreign");
    }
}