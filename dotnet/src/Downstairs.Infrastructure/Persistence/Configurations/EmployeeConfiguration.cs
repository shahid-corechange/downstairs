using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class EmployeeConfiguration : IEntityTypeConfiguration<Employee>
{
    public void Configure(EntityTypeBuilder<Employee> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("employees")
            .UseCollation("utf8mb4_unicode_ci");

        entity.HasIndex(e => e.AddressId, "employees_address_id_foreign");

        entity.HasIndex(e => e.UserId, "employees_user_id_foreign");

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.AddressId).HasColumnName("address_id");
        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");
        entity.Property(e => e.DeletedAt)
            .HasColumnType("timestamp")
            .HasColumnName("deleted_at");
        entity.Property(e => e.DialCode)
            .HasMaxLength(255)
            .HasColumnName("dial_code");
        entity.Property(e => e.Email)
            .HasMaxLength(255)
            .HasColumnName("email");
        entity.Property(e => e.FortnoxId)
            .HasColumnType("text")
            .HasColumnName("fortnox_id");
        entity.Property(e => e.IdentityNumber)
            .HasColumnType("text")
            .HasColumnName("identity_number");
        entity.Property(e => e.IsValidIdentity).HasColumnName("is_valid_identity");
        entity.Property(e => e.Name)
            .HasMaxLength(255)
            .HasColumnName("name");
        entity.Property(e => e.Phone1)
            .HasMaxLength(255)
            .HasColumnName("phone1");
        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");
        entity.Property(e => e.UserId).HasColumnName("user_id");

        entity.HasOne(d => d.Address).WithMany(p => p.Employees)
            .HasForeignKey(d => d.AddressId)
            .HasConstraintName("employees_address_id_foreign");

        entity.HasOne(d => d.User).WithMany(p => p.Employees)
            .HasForeignKey(d => d.UserId)
            .HasConstraintName("employees_user_id_foreign");
    }
}

