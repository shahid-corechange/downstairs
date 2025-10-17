using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class LeaveRegistrationConfiguration : IEntityTypeConfiguration<LeaveRegistration>
{
    public void Configure(EntityTypeBuilder<LeaveRegistration> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("leave_registrations")
            .UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasIndex(e => e.EmployeeId, "leave_registrations_employee_id_foreign");

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");
        entity.Property(e => e.DeletedAt)
            .HasColumnType("timestamp")
            .HasColumnName("deleted_at");
        entity.Property(e => e.EmployeeId).HasColumnName("employee_id");
        entity.Property(e => e.EndAt)
            .HasColumnType("datetime")
            .HasColumnName("end_at");
        entity.Property(e => e.IsStopped).HasColumnName("is_stopped");
        entity.Property(e => e.StartAt)
            .HasColumnType("datetime")
            .HasColumnName("start_at");
        entity.Property(e => e.Type)
            .HasMaxLength(255)
            .HasColumnName("type");
        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.HasOne(d => d.Employee).WithMany(p => p.LeaveRegistrations)
            .HasForeignKey(d => d.EmployeeId)
            .HasConstraintName("leave_registrations_employee_id_foreign");
    }
}