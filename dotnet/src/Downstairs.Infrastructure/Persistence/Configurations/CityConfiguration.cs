using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class CityConfiguration : IEntityTypeConfiguration<City>
{
    public void Configure(EntityTypeBuilder<City> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("cities")
            .UseCollation("utf8mb4_unicode_ci");

        entity.HasIndex(e => e.CountryId, "cities_country_id_foreign");

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.CountryId).HasColumnName("country_id");
        entity.Property(e => e.Name)
            .HasMaxLength(255)
            .HasColumnName("name");

        entity.HasOne(d => d.Country).WithMany(p => p.Cities)
            .HasForeignKey(d => d.CountryId)
            .HasConstraintName("cities_country_id_foreign");
    }
}

