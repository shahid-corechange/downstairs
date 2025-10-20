using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class TimeAdjustmentConfiguration : IEntityTypeConfiguration<TimeAdjustment>
{
    public void Configure(EntityTypeBuilder<TimeAdjustment> entity)
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

        entity.Property(e => e.Quarters)
            .HasColumnType("tinyint")
            .HasColumnName("quarters");

        entity.Property(e => e.Reason)
            .IsRequired()
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("reason");

        entity.Property(e => e.ScheduleEmployeeId)
            .HasColumnType("bigint")
            .HasColumnName("schedule_employee_id");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.CauserId, "time_adjustments_causer_id_foreign");

        entity.HasIndex(e => e.ScheduleEmployeeId, "time_adjustments_schedule_employee_id_foreign");

        entity.ToTable("time_adjustments").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.Causer)
            .WithMany(p => p.TimeAdjustments)
            .HasForeignKey(d => d.CauserId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("time_adjustments_causer_id_foreign");

        entity.HasOne(d => d.ScheduleEmployee)
            .WithMany(p => p.TimeAdjustments)
            .HasForeignKey(d => d.ScheduleEmployeeId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("time_adjustments_schedule_employee_id_foreign");
    }
}