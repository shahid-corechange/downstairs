using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class ModelHasRoleConfiguration : IEntityTypeConfiguration<ModelHasRole>
{
    public void Configure(EntityTypeBuilder<ModelHasRole> entity)
    {
        entity.Property(e => e.RoleId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("role_id");

        entity.Property(e => e.ModelId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("model_id");

        entity.Property(e => e.ModelType)
            .HasColumnType("varchar(255)")
            .HasColumnName("model_type");

        entity.HasKey(e => new { e.RoleId, e.ModelId, e.ModelType })
            .HasName("PRIMARY")
            .HasAnnotation("MySql:IndexPrefixLength", new[] { 0, 0, 0 });

        entity.HasIndex(e => new { e.ModelId, e.ModelType }, "model_has_roles_model_id_model_type_index");

        entity.ToTable("model_has_roles").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.Role)
            .WithMany(p => p.ModelHasRoles)
            .HasForeignKey(d => d.RoleId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("model_has_roles_role_id_foreign");
    }
}