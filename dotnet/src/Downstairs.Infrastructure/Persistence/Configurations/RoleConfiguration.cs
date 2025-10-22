using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class RoleConfiguration : IEntityTypeConfiguration<Role>
{
    public void Configure(EntityTypeBuilder<Role> entity)
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

        entity.HasIndex(e => new { e.Name, e.GuardName }, "roles_name_guard_name_unique")
            .IsUnique();

        entity.ToTable("roles").UseCollation(DatabaseConstants.Collations.Unicode);
    }
}