using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class ProductConfiguration : IEntityTypeConfiguration<Product>
{
    public void Configure(EntityTypeBuilder<Product> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("products")
            .UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasIndex(e => e.CategoryId, "products_category_id_foreign");

        entity.HasIndex(e => e.ServiceId, "products_service_id_foreign");

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.CategoryId).HasColumnName("category_id");
        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");
        entity.Property(e => e.CreditPrice).HasColumnName("credit_price");
        entity.Property(e => e.DeletedAt)
            .HasColumnType("timestamp")
            .HasColumnName("deleted_at");
        entity.Property(e => e.FortnoxArticleId)
            .HasMaxLength(255)
            .HasColumnName("fortnox_article_id");
        entity.Property(e => e.HasRut).HasColumnName("has_rut");
        entity.Property(e => e.InApp).HasColumnName("in_app");
        entity.Property(e => e.InStore).HasColumnName("in_store");
        entity.Property(e => e.Price)
            .HasColumnType("decimal(8,2) unsigned")
            .HasColumnName("price");
        entity.Property(e => e.ServiceId).HasColumnName("service_id");
        entity.Property(e => e.ThumbnailImage)
            .HasColumnType("text")
            .HasColumnName("thumbnail_image");
        entity.Property(e => e.Unit)
            .HasMaxLength(255)
            .HasColumnName("unit");
        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");
        entity.Property(e => e.VatGroup)
            .HasDefaultValueSql("'25'")
            .HasColumnName("vat_group");

        entity.HasOne(d => d.Category).WithMany(p => p.Products)
            .HasForeignKey(d => d.CategoryId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("products_category_id_foreign");

        entity.HasOne(d => d.Service).WithMany(p => p.Products)
            .HasForeignKey(d => d.ServiceId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("products_service_id_foreign");
    }
}