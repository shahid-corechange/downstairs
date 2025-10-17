using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class MonthlyWorkHourConfiguration : IEntityTypeConfiguration<MonthlyWorkHour>
{
    public void Configure(EntityTypeBuilder<MonthlyWorkHour> entity)
    {
        entity
            .HasNoKey()
            .ToView("monthly_work_hours");

        entity.Property(e => e.AdjustmentHours)
            .HasPrecision(51, 4)
            .HasColumnName("adjustment_hours");
        entity.Property(e => e.BookingHours)
            .HasPrecision(47, 4)
            .HasColumnName("booking_hours");
        entity.Property(e => e.FortnoxId)
            .HasColumnType("text")
            .HasColumnName("fortnox_id")
            .UseCollation("utf8mb4_unicode_ci");
        entity.Property(e => e.Fullname)
            .HasMaxLength(511)
            .HasColumnName("fullname")
            .UseCollation("utf8mb4_unicode_ci");
        entity.Property(e => e.Month).HasColumnName("month");
        entity.Property(e => e.ScheduleCleaningDeviation).HasColumnName("schedule_cleaning_deviation");
        entity.Property(e => e.ScheduleEmployeeDeviation).HasColumnName("schedule_employee_deviation");
        entity.Property(e => e.TotalWorkHours)
            .HasPrecision(47, 4)
            .HasColumnName("total_work_hours");
        entity.Property(e => e.UserId).HasColumnName("user_id");
        entity.Property(e => e.Year).HasColumnName("year");
    }
}

