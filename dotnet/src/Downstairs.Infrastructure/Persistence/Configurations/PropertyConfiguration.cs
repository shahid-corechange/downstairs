using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class PropertyConfiguration : IEntityTypeConfiguration<Property>
{
    public void Configure(EntityTypeBuilder<Property> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint unsigned")
            .HasColumnName("id");

        entity.Property(e => e.AddressId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("address_id");

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
            .IsRequired()
            .ValueGeneratedOnAdd()
            .HasColumnType("varchar(255)")
            .HasColumnName("membership_type")
            .HasDefaultValueSql("'private'");

        entity.Property(e => e.PropertyTypeId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("property_type_id");

        entity.Property(e => e.SquareMeter)
            .HasPrecision(8, 2)
            .HasColumnType("decimal(8,2) unsigned")
            .HasColumnName("square_meter");

        entity.Property(e => e.Status)
            .IsRequired()
            .ValueGeneratedOnAdd()
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("status")
            .HasDefaultValueSql("'active'");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.AddressId, "properties_address_id_foreign");

        entity.HasIndex(e => e.MembershipType, "properties_membership_type_index");

        entity.HasIndex(e => e.PropertyTypeId, "properties_property_type_id_foreign");

        entity.ToTable("properties").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.Address)
            .WithMany(p => p.Properties)
            .HasForeignKey(d => d.AddressId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("properties_address_id_foreign");

        entity.HasOne(d => d.PropertyType)
            .WithMany(p => p.Properties)
            .HasForeignKey(d => d.PropertyTypeId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("properties_property_type_id_foreign");
    }
}