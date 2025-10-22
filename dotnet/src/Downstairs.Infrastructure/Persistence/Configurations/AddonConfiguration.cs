using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class AddonConfiguration : IEntityTypeConfiguration<Addon>
{
    public void Configure(EntityTypeBuilder<Addon> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint unsigned")
            .HasColumnName("id");

        entity.Property(e => e.Color)
            .IsRequired()
            .ValueGeneratedOnAdd()
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("color")
            .HasDefaultValueSql("'#718096'");

        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");

        entity.Property(e => e.CreditPrice)
            .HasColumnType("smallint unsigned")
            .HasColumnName("credit_price");

        entity.Property(e => e.DeletedAt)
            .HasColumnType("timestamp")
            .HasColumnName("deleted_at");

        entity.Property(e => e.FortnoxArticleId)
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("fortnox_article_id");

        entity.Property(e => e.HasRut)
            .HasColumnType("tinyint(1)")
            .HasColumnName("has_rut");

        entity.Property(e => e.Price)
            .HasColumnType("decimal(8,2)")
            .HasColumnName("price");

        entity.Property(e => e.ThumbnailImage)
            .HasColumnType("text")
            .HasColumnName("thumbnail_image");

        entity.Property(e => e.Unit)
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("unit");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.Property(e => e.VatGroup)
            .ValueGeneratedOnAdd()
            .HasColumnType("tinyint unsigned")
            .HasColumnName("vat_group")
            .HasDefaultValueSql("'25'");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.ToTable("addons").UseCollation(DatabaseConstants.Collations.Unicode);
    }
}