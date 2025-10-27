using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class ScheduleEmployeeConfiguration : IEntityTypeConfiguration<ScheduleEmployee>
{
    public void Configure(EntityTypeBuilder<ScheduleEmployee> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint unsigned")
            .HasColumnName("id");

        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");

        entity.Property(e => e.DeletedAt)
            .HasColumnType("timestamp")
            .HasColumnName("deleted_at");

        entity.Property(e => e.Description)
            .HasColumnType("text")
            .HasColumnName("description");

        entity.Property(e => e.EndAt)
            .HasColumnType("timestamp")
            .HasColumnName("end_at");

        entity.Property(e => e.EndIp)
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("end_ip");

        entity.Property(e => e.EndLatitude)
            .HasPrecision(10, 2)
            .HasColumnType("decimal(10,2) unsigned")
            .HasColumnName("end_latitude");

        entity.Property(e => e.EndLongitude)
            .HasPrecision(10, 2)
            .HasColumnType("decimal(10,2) unsigned")
            .HasColumnName("end_longitude");

        entity.Property(e => e.ScheduleId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("schedule_id");

        entity.Property(e => e.StartAt)
            .HasColumnType("timestamp")
            .HasColumnName("start_at");

        entity.Property(e => e.StartIp)
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("start_ip");

        entity.Property(e => e.StartLatitude)
            .HasPrecision(10, 2)
            .HasColumnType("decimal(10,2) unsigned")
            .HasColumnName("start_latitude");

        entity.Property(e => e.StartLongitude)
            .HasPrecision(10, 2)
            .HasColumnType("decimal(10,2) unsigned")
            .HasColumnName("start_longitude");

        entity.Property(e => e.Status)
            .IsRequired()
            .ValueGeneratedOnAdd()
            .HasColumnType("varchar(255)")
            .HasColumnName("status")
            .HasDefaultValueSql("'pending'");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.Property(e => e.UserId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("user_id");

        entity.Property(e => e.WorkHourId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("work_hour_id");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.ScheduleId, "schedule_employees_schedule_id_foreign");

        entity.HasIndex(e => e.Status, "schedule_employees_status_index");

        entity.HasIndex(e => e.UserId, "schedule_employees_user_id_foreign");

        entity.HasIndex(e => e.WorkHourId, "schedule_employees_work_hour_id_foreign");

        entity.ToTable("schedule_employees").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.Schedule)
            .WithMany(p => p.ScheduleEmployees)
            .HasForeignKey(d => d.ScheduleId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("schedule_employees_schedule_id_foreign");

        entity.HasOne(d => d.User)
            .WithMany(p => p.ScheduleEmployees)
            .HasForeignKey(d => d.UserId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("schedule_employees_user_id_foreign");

        entity.HasOne(d => d.WorkHour)
            .WithMany(p => p.ScheduleEmployees)
            .HasForeignKey(d => d.WorkHourId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("schedule_employees_work_hour_id_foreign");
    }
}