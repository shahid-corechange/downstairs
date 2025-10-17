using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class PropertyConfiguration : IEntityTypeConfiguration<Property>
{
    public void Configure(EntityTypeBuilder<Property> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("properties")
            .UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasIndex(e => e.AddressId, "properties_address_id_foreign");

        entity.HasIndex(e => e.MembershipType, "properties_membership_type_index");

        entity.HasIndex(e => e.PropertyTypeId, "properties_property_type_id_foreign");

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.AddressId).HasColumnName("address_id");
        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");
        entity.Property(e => e.DeletedAt)
            .HasColumnType("timestamp")
            .HasColumnName("deleted_at");
        entity.Property(e => e.KeyInformation)
            .HasColumnType("json")
            .HasColumnName("key_information");
        entity.Property(e => e.MembershipType)
            .HasDefaultValueSql("'private'")
            .HasColumnName("membership_type");
        entity.Property(e => e.PropertyTypeId).HasColumnName("property_type_id");
        entity.Property(e => e.SquareMeter)
            .HasPrecision(8, 2)
            .HasColumnName("square_meter");
        entity.Property(e => e.Status)
            .HasMaxLength(255)
            .HasDefaultValueSql("'active'")
            .HasColumnName("status");
        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.HasOne(d => d.Address).WithMany(p => p.Properties)
            .HasForeignKey(d => d.AddressId)
            .HasConstraintName("properties_address_id_foreign");

        entity.HasOne(d => d.PropertyType).WithMany(p => p.Properties)
            .HasForeignKey(d => d.PropertyTypeId)
            .HasConstraintName("properties_property_type_id_foreign");
    }
}