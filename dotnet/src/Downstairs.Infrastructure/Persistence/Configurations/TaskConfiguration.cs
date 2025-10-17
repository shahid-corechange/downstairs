using Downstairs.Infrastructure.Persistence.Constants;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;
using TaskEntity = Downstairs.Infrastructure.Persistence.Models.Task;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class TaskConfiguration : IEntityTypeConfiguration<TaskEntity>
{
    public void Configure(EntityTypeBuilder<TaskEntity> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("tasks")
            .UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasIndex(e => e.CustomTaskId, "tasks_custom_task_id_foreign");

        entity.HasIndex(e => e.ScheduleEmployeeId, "tasks_schedule_employee_id_foreign");

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.CustomTaskId).HasColumnName("custom_task_id");
        entity.Property(e => e.Description)
            .HasColumnType("text")
            .HasColumnName("description");
        entity.Property(e => e.IsCompleted).HasColumnName("is_completed");
        entity.Property(e => e.Name)
            .HasMaxLength(255)
            .HasColumnName("name");
        entity.Property(e => e.ScheduleEmployeeId).HasColumnName("schedule_employee_id");

        entity.HasOne(d => d.CustomTask).WithMany(p => p.Tasks)
            .HasForeignKey(d => d.CustomTaskId)
            .HasConstraintName("tasks_custom_task_id_foreign");

        entity.HasOne(d => d.ScheduleEmployee).WithMany(p => p.Tasks)
            .HasForeignKey(d => d.ScheduleEmployeeId)
            .HasConstraintName("tasks_schedule_employee_id_foreign");
    }
}