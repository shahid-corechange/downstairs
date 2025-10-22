using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class StoreProductConfiguration : IEntityTypeConfiguration<StoreProduct>
{
    public void Configure(EntityTypeBuilder<StoreProduct> entity)
    {
        entity.Property(e => e.StoreId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("store_id");

        entity.Property(e => e.ProductId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("product_id");

        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");

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

        entity.HasKey(e => new { e.StoreId, e.ProductId })
            .HasName("PRIMARY")
            .HasAnnotation("MySql:IndexPrefixLength", new[] { 0, 0 });

        entity.HasIndex(e => e.ProductId, "store_products_product_id_foreign");

        entity.ToTable("store_products").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.Product)
            .WithMany(p => p.StoreProducts)
            .HasForeignKey(d => d.ProductId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("store_products_product_id_foreign");

        entity.HasOne(d => d.Store)
            .WithMany(p => p.StoreProducts)
            .HasForeignKey(d => d.StoreId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("store_products_store_id_foreign");
    }
}