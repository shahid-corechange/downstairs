using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class CashierAttendanceConfiguration : IEntityTypeConfiguration<CashierAttendance>
{
    public void Configure(EntityTypeBuilder<CashierAttendance> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint")
            .HasColumnName("id");

        entity.Property(e => e.CheckInAt)
            .HasColumnType("timestamp")
            .HasColumnName("check_in_at");

        entity.Property(e => e.CheckInCauserId)
            .HasColumnType("bigint")
            .HasColumnName("check_in_causer_id");

        entity.Property(e => e.CheckOutAt)
            .HasColumnType("timestamp")
            .HasColumnName("check_out_at");

        entity.Property(e => e.CheckOutCauserId)
            .HasColumnType("bigint")
            .HasColumnName("check_out_causer_id");

        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");

        entity.Property(e => e.DeletedAt)
            .HasColumnType("timestamp")
            .HasColumnName("deleted_at");

        entity.Property(e => e.StoreId)
            .HasColumnType("bigint")
            .HasColumnName("store_id");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.Property(e => e.UserId)
            .HasColumnType("bigint")
            .HasColumnName("user_id");

        entity.Property(e => e.WorkHourId)
            .HasColumnType("bigint")
            .HasColumnName("work_hour_id");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.CheckInCauserId, "cashier_attendances_check_in_causer_id_foreign");

        entity.HasIndex(e => e.CheckOutCauserId, "cashier_attendances_check_out_causer_id_foreign");

        entity.HasIndex(e => e.StoreId, "cashier_attendances_store_id_foreign");

        entity.HasIndex(e => e.UserId, "cashier_attendances_user_id_foreign");

        entity.HasIndex(e => e.WorkHourId, "cashier_attendances_work_hour_id_foreign");

        entity.ToTable("cashier_attendances").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.CheckInCauser)
            .WithMany(p => p.CashierAttendanceCheckInCausers)
            .HasForeignKey(d => d.CheckInCauserId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("cashier_attendances_check_in_causer_id_foreign");

        entity.HasOne(d => d.CheckOutCauser)
            .WithMany(p => p.CashierAttendanceCheckOutCausers)
            .HasForeignKey(d => d.CheckOutCauserId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("cashier_attendances_check_out_causer_id_foreign");

        entity.HasOne(d => d.Store)
            .WithMany(p => p.CashierAttendances)
            .HasForeignKey(d => d.StoreId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("cashier_attendances_store_id_foreign");

        entity.HasOne(d => d.User)
            .WithMany(p => p.CashierAttendanceUsers)
            .HasForeignKey(d => d.UserId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("cashier_attendances_user_id_foreign");

        entity.HasOne(d => d.WorkHour)
            .WithMany(p => p.CashierAttendances)
            .HasForeignKey(d => d.WorkHourId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("cashier_attendances_work_hour_id_foreign");
    }
}