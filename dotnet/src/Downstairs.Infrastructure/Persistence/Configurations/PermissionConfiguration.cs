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
            .UsingEntity<System.Collections.Generic.Dictionary<string, object>>(
                "role_has_permissions",
                l => l.HasOne<Role>()
                    .WithMany()
                    .HasForeignKey("role_id")
                    .HasPrincipalKey(nameof(Role.Id))
                    .OnDelete(DeleteBehavior.Cascade)
                    .HasConstraintName("role_has_permissions_role_id_foreign"),
                r => r.HasOne<Permission>()
                    .WithMany()
                    .HasForeignKey("permission_id")
                    .HasPrincipalKey(nameof(Permission.Id))
                    .OnDelete(DeleteBehavior.Cascade)
                    .HasConstraintName("role_has_permissions_permission_id_foreign"),
                j =>
                {
                    j.ToTable("role_has_permissions");
                    j.HasKey("permission_id", "role_id");
                });

        entity.ToTable("permissions").UseCollation(DatabaseConstants.Collations.Unicode);
    }
}