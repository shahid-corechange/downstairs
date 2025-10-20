using Downstairs.Infrastructure.Persistence.Constants;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;
using TaskEntity = Downstairs.Infrastructure.Persistence.Models.Task;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class TaskConfiguration : IEntityTypeConfiguration<TaskEntity>
{
    public void Configure(EntityTypeBuilder<TaskEntity> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint")
            .HasColumnName("id");

        entity.Property(e => e.CustomTaskId)
            .HasColumnType("bigint")
            .HasColumnName("custom_task_id");

        entity.Property(e => e.Description)
            .HasColumnType("text")
            .HasColumnName("description");

        entity.Property(e => e.IsCompleted)
            .HasColumnType("tinyint(1)")
            .HasColumnName("is_completed");

        entity.Property(e => e.Name)
            .IsRequired()
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("name");

        entity.Property(e => e.ScheduleEmployeeId)
            .HasColumnType("bigint")
            .HasColumnName("schedule_employee_id");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.CustomTaskId, "tasks_custom_task_id_foreign");

        entity.HasIndex(e => e.ScheduleEmployeeId, "tasks_schedule_employee_id_foreign");

        entity.ToTable("tasks").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.CustomTask)
            .WithMany(p => p.Tasks)
            .HasForeignKey(d => d.CustomTaskId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("tasks_custom_task_id_foreign");

        entity.HasOne(d => d.ScheduleEmployee)
            .WithMany(p => p.Tasks)
            .HasForeignKey(d => d.ScheduleEmployeeId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("tasks_schedule_employee_id_foreign");
    }
}