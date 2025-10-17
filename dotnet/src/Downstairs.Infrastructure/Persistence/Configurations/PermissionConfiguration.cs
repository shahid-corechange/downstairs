using System.Collections.Generic;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class PermissionConfiguration : IEntityTypeConfiguration<Permission>
{
    public void Configure(EntityTypeBuilder<Permission> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("permissions")
            .UseCollation("utf8mb4_unicode_ci");

        entity.HasIndex(e => new { e.Name, e.GuardName }, "permissions_name_guard_name_unique").IsUnique();

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");
        entity.Property(e => e.GuardName).HasColumnName("guard_name");
        entity.Property(e => e.Name).HasColumnName("name");
        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.HasMany(d => d.Roles).WithMany(p => p.Permissions)
            .UsingEntity<Dictionary<string, object>>(
                "RoleHasPermission",
                r => r.HasOne<Role>().WithMany()
                    .HasForeignKey("RoleId")
                    .HasConstraintName("role_has_permissions_role_id_foreign"),
                l => l.HasOne<Permission>().WithMany()
                    .HasForeignKey("PermissionId")
                    .HasConstraintName("role_has_permissions_permission_id_foreign"),
                j =>
                {
                    j.HasKey("PermissionId", "RoleId")
                        .HasName("PRIMARY")
                        .HasAnnotation("MySql:IndexPrefixLength", new[] { 0, 0 });
                    j
                        .ToTable("role_has_permissions")
                        .UseCollation("utf8mb4_unicode_ci");
                    j.HasIndex(new[] { "RoleId" }, "role_has_permissions_role_id_foreign");
                    j.IndexerProperty<ulong>("PermissionId").HasColumnName("permission_id");
                    j.IndexerProperty<ulong>("RoleId").HasColumnName("role_id");
                });
    }
}


