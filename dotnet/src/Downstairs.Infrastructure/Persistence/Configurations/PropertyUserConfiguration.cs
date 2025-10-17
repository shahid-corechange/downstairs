using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class PropertyUserConfiguration : IEntityTypeConfiguration<PropertyUser>
{
    public void Configure(EntityTypeBuilder<PropertyUser> entity)
    {
        entity.HasKey(e => e.MyRowId).HasName("PRIMARY");

        entity
            .ToTable("property_user")
            .UseCollation("utf8mb4_unicode_ci");

        entity.HasIndex(e => e.PropertyId, "property_user_property_id_foreign");

        entity.HasIndex(e => e.UserId, "property_user_user_id_foreign");

        entity.Property(e => e.MyRowId).HasColumnName("my_row_id");
        entity.Property(e => e.PropertyId).HasColumnName("property_id");
        entity.Property(e => e.UserId).HasColumnName("user_id");

        entity.HasOne(d => d.Property).WithMany(p => p.PropertyUsers)
            .HasForeignKey(d => d.PropertyId)
            .HasConstraintName("property_user_property_id_foreign");

        entity.HasOne(d => d.User).WithMany(p => p.PropertyUsers)
            .HasForeignKey(d => d.UserId)
            .HasConstraintName("property_user_user_id_foreign");
    }
}

