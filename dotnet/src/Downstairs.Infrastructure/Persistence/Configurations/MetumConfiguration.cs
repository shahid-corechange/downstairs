using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class MetumConfiguration : IEntityTypeConfiguration<Metum>
{
    public void Configure(EntityTypeBuilder<Metum> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("meta")
            .UseCollation("utf8mb4_unicode_ci");

        entity.HasIndex(e => new { e.MetableId, e.MetableType, e.Key, e.PublishedAt }, "meta_metable_id_metable_type_key_published_at_index");

        entity.HasIndex(e => new { e.MetableId, e.MetableType, e.PublishedAt }, "meta_metable_id_metable_type_published_at_index");

        entity.HasIndex(e => new { e.MetableType, e.MetableId }, "meta_metable_type_metable_id_index");

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");
        entity.Property(e => e.Key).HasColumnName("key");
        entity.Property(e => e.MetableId).HasColumnName("metable_id");
        entity.Property(e => e.MetableType).HasColumnName("metable_type");
        entity.Property(e => e.PublishedAt)
            .HasColumnType("datetime")
            .HasColumnName("published_at");
        entity.Property(e => e.Type)
            .HasMaxLength(255)
            .HasColumnName("type");
        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");
        entity.Property(e => e.Value).HasColumnName("value");
    }
}

