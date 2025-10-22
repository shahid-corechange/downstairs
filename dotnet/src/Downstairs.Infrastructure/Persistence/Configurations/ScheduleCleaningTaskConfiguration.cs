using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class ScheduleCleaningTaskConfiguration : IEntityTypeConfiguration<ScheduleCleaningTask>
{
    public void Configure(EntityTypeBuilder<ScheduleCleaningTask> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint unsigned")
            .HasColumnName("id");

        entity.Property(e => e.CustomTaskId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("custom_task_id");

        entity.Property(e => e.IsCompleted)
            .HasColumnType("tinyint(1)")
            .HasColumnName("is_completed")
            .HasDefaultValueSql("'0'");

        entity.Property(e => e.ScheduleCleaningId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("schedule_cleaning_id");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.CustomTaskId, "schedule_cleaning_tasks_custom_task_id_foreign");

        entity.HasIndex(e => e.ScheduleCleaningId, "schedule_cleaning_tasks_schedule_cleaning_id_foreign");

        entity.ToTable("schedule_cleaning_tasks").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.CustomTask)
            .WithMany(p => p.ScheduleCleaningTasks)
            .HasForeignKey(d => d.CustomTaskId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("schedule_cleaning_tasks_custom_task_id_foreign");

        entity.HasOne(d => d.ScheduleCleaning)
            .WithMany(p => p.ScheduleCleaningTasks)
            .HasForeignKey(d => d.ScheduleCleaningId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("schedule_cleaning_tasks_schedule_cleaning_id_foreign");
    }
}