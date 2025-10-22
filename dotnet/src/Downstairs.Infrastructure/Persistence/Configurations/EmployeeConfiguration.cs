using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class EmployeeConfiguration : IEntityTypeConfiguration<Employee>
{
    public void Configure(EntityTypeBuilder<Employee> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint unsigned")
            .HasColumnName("id");

        entity.Property(e => e.AddressId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("address_id");

        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");

        entity.Property(e => e.DeletedAt)
            .HasColumnType("timestamp")
            .HasColumnName("deleted_at");

        entity.Property(e => e.DialCode)
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("dial_code");

        entity.Property(e => e.Email)
            .IsRequired()
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("email");

        entity.Property(e => e.FortnoxId)
            .HasColumnType("text")
            .HasColumnName("fortnox_id");

        entity.Property(e => e.IdentityNumber)
            .IsRequired()
            .HasColumnType("text")
            .HasColumnName("identity_number");

        entity.Property(e => e.IsValidIdentity)
            .HasColumnType("tinyint(1)")
            .HasDefaultValueSql("'0'")
            .HasColumnName("is_valid_identity");

        entity.Property(e => e.Name)
            .IsRequired()
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("name");

        entity.Property(e => e.Phone1)
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("phone1");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.Property(e => e.UserId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("user_id");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.AddressId, "employees_address_id_foreign");

        entity.HasIndex(e => e.UserId, "employees_user_id_foreign");

        entity.ToTable("employees").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.Address)
            .WithMany(p => p.Employees)
            .HasForeignKey(d => d.AddressId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("employees_address_id_foreign");

        entity.HasOne(d => d.User)
            .WithMany(p => p.Employees)
            .HasForeignKey(d => d.UserId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("employees_user_id_foreign");
    }
}