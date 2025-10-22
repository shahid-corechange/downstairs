using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class ModelHasPermissionConfiguration : IEntityTypeConfiguration<ModelHasPermission>
{
    public void Configure(EntityTypeBuilder<ModelHasPermission> entity)
    {
        entity.Property(e => e.PermissionId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("permission_id");

        entity.Property(e => e.ModelId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("model_id");

        entity.Property(e => e.ModelType)
            .HasColumnType("varchar(255)")
            .HasColumnName("model_type");

        entity.HasKey(e => new { e.PermissionId, e.ModelId, e.ModelType })
            .HasName("PRIMARY")
            .HasAnnotation("MySql:IndexPrefixLength", new[] { 0, 0, 0 });

        entity.HasIndex(e => new { e.ModelId, e.ModelType }, "model_has_permissions_model_id_model_type_index");

        entity.ToTable("model_has_permissions").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.Permission)
            .WithMany(p => p.ModelHasPermissions)
            .HasForeignKey(d => d.PermissionId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("model_has_permissions_permission_id_foreign");
    }
}