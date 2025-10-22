using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class KeyPlaceConfiguration : IEntityTypeConfiguration<KeyPlace>
{
    public void Configure(EntityTypeBuilder<KeyPlace> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint unsigned")
            .HasColumnName("id");

        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");

        entity.Property(e => e.DeletedAt)
            .HasColumnType("timestamp")
            .HasColumnName("deleted_at");

        entity.Property(e => e.PropertyId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("property_id");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.PropertyId, "key_places_property_id_foreign");

        entity.ToTable("key_places").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.Property)
            .WithMany(p => p.KeyPlaces)
            .HasForeignKey(d => d.PropertyId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("key_places_property_id_foreign");
    }
}