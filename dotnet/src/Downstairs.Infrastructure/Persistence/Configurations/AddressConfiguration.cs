using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class AddressConfiguration : IEntityTypeConfiguration<Address>
{
    public void Configure(EntityTypeBuilder<Address> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("addresses")
            .UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasIndex(e => e.CityId, "addresses_city_id_foreign");

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.Accuracy)
            .HasPrecision(11, 8)
            .HasColumnName("accuracy");
        entity.Property(e => e.Address1)
            .HasColumnType("text")
            .HasColumnName("address");
        entity.Property(e => e.Address2)
            .HasColumnType("text")
            .HasColumnName("address_2");
        entity.Property(e => e.Area)
            .HasMaxLength(255)
            .HasColumnName("area");
        entity.Property(e => e.CityId).HasColumnName("city_id");
        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");
        entity.Property(e => e.DeletedAt)
            .HasColumnType("timestamp")
            .HasColumnName("deleted_at");
        entity.Property(e => e.Latitude)
            .HasPrecision(11, 8)
            .HasColumnName("latitude");
        entity.Property(e => e.Longitude)
            .HasPrecision(11, 8)
            .HasColumnName("longitude");
        entity.Property(e => e.PostalCode)
            .HasMaxLength(255)
            .HasColumnName("postal_code");
        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.HasOne(d => d.City).WithMany(p => p.Addresses)
            .HasForeignKey(d => d.CityId)
            .HasConstraintName("addresses_city_id_foreign");
    }
}