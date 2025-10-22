using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class LeaveRegistrationConfiguration : IEntityTypeConfiguration<LeaveRegistration>
{
    public void Configure(EntityTypeBuilder<LeaveRegistration> entity)
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

        entity.Property(e => e.EmployeeId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("employee_id");

        entity.Property(e => e.EndAt)
            .HasColumnType("datetime")
            .HasColumnName("end_at");

        entity.Property(e => e.IsStopped)
            .HasColumnType("tinyint(1)")
            .HasDefaultValueSql("'0'")
            .HasColumnName("is_stopped");

        entity.Property(e => e.StartAt)
            .HasColumnType("datetime")
            .HasColumnName("start_at");

        entity.Property(e => e.Type)
            .IsRequired()
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("type");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.EmployeeId, "leave_registrations_employee_id_foreign");

        entity.ToTable("leave_registrations").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.Employee)
            .WithMany(p => p.LeaveRegistrations)
            .HasForeignKey(d => d.EmployeeId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("leave_registrations_employee_id_foreign");
    }
}