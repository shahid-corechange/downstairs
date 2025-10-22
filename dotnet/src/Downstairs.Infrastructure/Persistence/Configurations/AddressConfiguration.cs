using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class AddressConfiguration : IEntityTypeConfiguration<Address>
{
    public void Configure(EntityTypeBuilder<Address> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint unsigned")
            .HasColumnName("id");

        entity.Property(e => e.Accuracy)
            .HasPrecision(11, 8)
            .HasColumnType("decimal(11,8) unsigned")
            .HasColumnName("accuracy");

        entity.Property(e => e.Address1)
            .IsRequired()
            .HasColumnType("text")
            .HasColumnName("address");

        entity.Property(e => e.Address2)
            .HasColumnType("text")
            .HasColumnName("address_2");

        entity.Property(e => e.Area)
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("area");

        entity.Property(e => e.CityId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("city_id");

        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");

        entity.Property(e => e.DeletedAt)
            .HasColumnType("timestamp")
            .HasColumnName("deleted_at");

        entity.Property(e => e.Latitude)
            .HasPrecision(11, 8)
            .HasColumnType("decimal(11,8) unsigned")
            .HasColumnName("latitude");

        entity.Property(e => e.Longitude)
            .HasPrecision(11, 8)
            .HasColumnType("decimal(11,8) unsigned")
            .HasColumnName("longitude");

        entity.Property(e => e.PostalCode)
            .IsRequired()
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("postal_code");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.CityId, "addresses_city_id_foreign");

        entity.ToTable("addresses").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.City)
            .WithMany(p => p.Addresses)
            .HasForeignKey(d => d.CityId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("addresses_city_id_foreign");
    }
}