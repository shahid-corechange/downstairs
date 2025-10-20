using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class WorkHourConfiguration : IEntityTypeConfiguration<WorkHour>
{
    public void Configure(EntityTypeBuilder<WorkHour> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint")
            .HasColumnName("id");

        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");

        entity.Property(e => e.Date)
            .HasColumnType("date")
            .HasColumnName("date");

        entity.Property(e => e.EndTime)
            .HasColumnType("time")
            .HasColumnName("end_time");

        entity.Property(e => e.FortnoxAttendanceId)
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("fortnox_attendance_id");

        entity.Property(e => e.StartTime)
            .HasColumnType("time")
            .HasColumnName("start_time");

        entity.Property(e => e.Type)
            .IsRequired()
            .ValueGeneratedOnAdd()
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("type")
            .HasDefaultValueSql("'schedule'");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.Property(e => e.UserId)
            .HasColumnType("bigint")
            .HasColumnName("user_id");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.Date, "work_hours_date_index");

        entity.HasIndex(e => e.UserId, "work_hours_user_id_foreign");

        entity.ToTable("work_hours").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.User)
            .WithMany(p => p.WorkHours)
            .HasForeignKey(d => d.UserId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("work_hours_user_id_foreign");
    }
}