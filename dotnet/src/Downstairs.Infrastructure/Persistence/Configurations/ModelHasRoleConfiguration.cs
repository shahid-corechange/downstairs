using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class ModelHasRoleConfiguration : IEntityTypeConfiguration<ModelHasRole>
{
    public void Configure(EntityTypeBuilder<ModelHasRole> entity)
    {
        entity.HasKey(e => new { e.RoleId, e.ModelId, e.ModelType })
            .HasName("PRIMARY")
            .HasAnnotation("MySql:IndexPrefixLength", new[] { 0, 0, 0 });

        entity
            .ToTable("model_has_roles")
            .UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasIndex(e => new { e.ModelId, e.ModelType }, "model_has_roles_model_id_model_type_index");

        entity.Property(e => e.RoleId).HasColumnName("role_id");
        entity.Property(e => e.ModelId).HasColumnName("model_id");
        entity.Property(e => e.ModelType).HasColumnName("model_type");

        entity.HasOne(d => d.Role).WithMany(p => p.ModelHasRoles)
            .HasForeignKey(d => d.RoleId)
            .HasConstraintName("model_has_roles_role_id_foreign");
    }
}