using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class ServiceConfiguration : IEntityTypeConfiguration<Service>
{
    public void Configure(EntityTypeBuilder<Service> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("services")
            .UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasIndex(e => e.Type, "services_type_index");

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");
        entity.Property(e => e.DeletedAt)
            .HasColumnType("timestamp")
            .HasColumnName("deleted_at");
        entity.Property(e => e.FortnoxArticleId)
            .HasMaxLength(255)
            .HasColumnName("fortnox_article_id");
        entity.Property(e => e.HasRut).HasColumnName("has_rut");
        entity.Property(e => e.Price)
            .HasColumnType("decimal(8,2) unsigned")
            .HasColumnName("price");
        entity.Property(e => e.ThumbnailImage)
            .HasColumnType("text")
            .HasColumnName("thumbnail_image");
        entity.Property(e => e.Type).HasColumnName("type");
        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");
        entity.Property(e => e.VatGroup)
            .HasDefaultValueSql("'25'")
            .HasColumnName("vat_group");
    }
}