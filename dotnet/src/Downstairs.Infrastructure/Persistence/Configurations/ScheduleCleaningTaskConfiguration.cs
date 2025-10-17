using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class ScheduleCleaningTaskConfiguration : IEntityTypeConfiguration<ScheduleCleaningTask>
{
    public void Configure(EntityTypeBuilder<ScheduleCleaningTask> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("schedule_cleaning_tasks")
            .UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasIndex(e => e.CustomTaskId, "schedule_cleaning_tasks_custom_task_id_foreign");

        entity.HasIndex(e => e.ScheduleCleaningId, "schedule_cleaning_tasks_schedule_cleaning_id_foreign");

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.CustomTaskId).HasColumnName("custom_task_id");
        entity.Property(e => e.IsCompleted).HasColumnName("is_completed");
        entity.Property(e => e.ScheduleCleaningId).HasColumnName("schedule_cleaning_id");

        entity.HasOne(d => d.CustomTask).WithMany(p => p.ScheduleCleaningTasks)
            .HasForeignKey(d => d.CustomTaskId)
            .HasConstraintName("schedule_cleaning_tasks_custom_task_id_foreign");

        entity.HasOne(d => d.ScheduleCleaning).WithMany(p => p.ScheduleCleaningTasks)
            .HasForeignKey(d => d.ScheduleCleaningId)
            .HasConstraintName("schedule_cleaning_tasks_schedule_cleaning_id_foreign");
    }
}