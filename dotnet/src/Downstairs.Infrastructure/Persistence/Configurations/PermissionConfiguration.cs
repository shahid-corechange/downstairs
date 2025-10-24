using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class PermissionConfiguration : IEntityTypeConfiguration<Permission>
{
    public void Configure(EntityTypeBuilder<Permission> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint unsigned")
            .HasColumnName("id");

        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");

        entity.Property(e => e.GuardName)
            .IsRequired()
            .HasColumnType("varchar(255)")
            .HasColumnName("guard_name");

        entity.Property(e => e.Name)
            .IsRequired()
            .HasColumnType("varchar(255)")
            .HasColumnName("name");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => new { e.Name, e.GuardName }, "permissions_name_guard_name_unique")
            .IsUnique();

        // Configure many-to-many relationship with Role using correct table name
        entity.HasMany(p => p.Roles)
            .WithMany(r => r.Permissions)
            .UsingEntity(
                "role_has_permissions",
                l => l.HasOne(typeof(Role)).WithMany().HasForeignKey("role_id").HasPrincipalKey(nameof(Role.Id)),
                r => r.HasOne(typeof(Permission)).WithMany().HasForeignKey("permission_id").HasPrincipalKey(nameof(Permission.Id)),
                j => j.HasKey("role_id", "permission_id"));

        entity.ToTable("permissions").UseCollation(DatabaseConstants.Collations.Unicode);
    }
}