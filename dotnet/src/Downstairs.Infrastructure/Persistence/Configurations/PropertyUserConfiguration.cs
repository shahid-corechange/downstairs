using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class PropertyUserConfiguration : IEntityTypeConfiguration<PropertyUser>
{
    public void Configure(EntityTypeBuilder<PropertyUser> entity)
    {
        entity.Property(e => e.MyRowId)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint")
            .HasColumnName("my_row_id");

        entity.Property(e => e.PropertyId)
            .HasColumnType("bigint")
            .HasColumnName("property_id");

        entity.Property(e => e.UserId)
            .HasColumnType("bigint")
            .HasColumnName("user_id");

        entity.HasKey(e => e.MyRowId)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.PropertyId, "property_user_property_id_foreign");

        entity.HasIndex(e => e.UserId, "property_user_user_id_foreign");

        entity.ToTable("property_user").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.Property)
            .WithMany(p => p.PropertyUsers)
            .HasForeignKey(d => d.PropertyId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("property_user_property_id_foreign");

        entity.HasOne(d => d.User)
            .WithMany(p => p.PropertyUsers)
            .HasForeignKey(d => d.UserId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("property_user_user_id_foreign");
    }
}