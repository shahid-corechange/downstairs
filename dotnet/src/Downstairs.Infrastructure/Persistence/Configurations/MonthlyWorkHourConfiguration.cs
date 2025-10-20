using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class MonthlyWorkHourConfiguration : IEntityTypeConfiguration<MonthlyWorkHour>
{
    public void Configure(EntityTypeBuilder<MonthlyWorkHour> entity)
    {
        entity.HasNoKey();

        entity.Property(e => e.AdjustmentHours)
            .HasPrecision(51, 4)
            .HasColumnType("decimal(51,4)")
            .HasColumnName("adjustment_hours");

        entity.Property(e => e.BookingHours)
            .HasPrecision(47, 4)
            .HasColumnType("decimal(47,4)")
            .HasColumnName("booking_hours");

        entity.Property(e => e.EmployeeId)
            .HasColumnType("bigint")
            .HasColumnName("employee_id")
            .HasDefaultValueSql("'0'");

        entity.Property(e => e.FortnoxId)
            .HasColumnType("text")
            .HasColumnName("fortnox_id")
            .UseCollation(DatabaseConstants.Collations.Unicode);

        entity.Property(e => e.Fullname)
            .HasMaxLength(511)
            .HasColumnType("varchar(511)")
            .HasColumnName("fullname")
            .UseCollation(DatabaseConstants.Collations.Unicode);

        entity.Property(e => e.Month)
            .HasColumnType("int")
            .HasColumnName("month");

        entity.Property(e => e.ScheduleDeviation)
            .HasColumnType("bigint")
            .HasColumnName("schedule_deviation");

        entity.Property(e => e.ScheduleEmployeeDeviation)
            .HasColumnType("bigint")
            .HasColumnName("schedule_employee_deviation");

        entity.Property(e => e.ScheduleWorkHours)
            .HasPrecision(47, 4)
            .HasColumnType("decimal(47,4)")
            .HasColumnName("schedule_work_hours");

        entity.Property(e => e.StoreWorkHours)
            .HasPrecision(47, 4)
            .HasColumnType("decimal(47,4)")
            .HasColumnName("store_work_hours");

        entity.Property(e => e.TotalWorkHours)
            .HasPrecision(48, 4)
            .HasColumnType("decimal(48,4)")
            .HasColumnName("total_work_hours");

        entity.Property(e => e.UserId)
            .HasColumnType("bigint")
            .HasColumnName("user_id");

        entity.Property(e => e.Year)
            .HasColumnType("int")
            .HasColumnName("year");

        entity.ToView("monthly_work_hours");
    }
}