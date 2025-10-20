using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class StoreSaleProductConfiguration : IEntityTypeConfiguration<StoreSaleProduct>
{
    public void Configure(EntityTypeBuilder<StoreSaleProduct> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint")
            .HasColumnName("id");

        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");

        entity.Property(e => e.Discount)
            .HasColumnType("decimal(8,2)")
            .HasColumnName("discount");

        entity.Property(e => e.Name)
            .IsRequired()
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("name");

        entity.Property(e => e.Note)
            .HasColumnType("text")
            .HasColumnName("note");

        entity.Property(e => e.Price)
            .HasColumnType("decimal(8,2)")
            .HasColumnName("price");

        entity.Property(e => e.ProductId)
            .HasColumnType("bigint")
            .HasColumnName("product_id");

        entity.Property(e => e.Quantity)
            .HasColumnType("tinyint")
            .HasColumnName("quantity");

        entity.Property(e => e.StoreSaleId)
            .HasColumnType("bigint")
            .HasColumnName("store_sale_id");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.Property(e => e.VatGroup)
            .ValueGeneratedOnAdd()
            .HasColumnType("tinyint")
            .HasColumnName("vat_group")
            .HasDefaultValueSql("'25'");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.ProductId, "store_sale_products_product_id_foreign");

        entity.HasIndex(e => e.StoreSaleId, "store_sale_products_store_sale_id_foreign");

        entity.ToTable("store_sale_products").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.Product)
            .WithMany(p => p.StoreSaleProducts)
            .HasForeignKey(d => d.ProductId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("store_sale_products_product_id_foreign");

        entity.HasOne(d => d.StoreSale)
            .WithMany(p => p.StoreSaleProducts)
            .HasForeignKey(d => d.StoreSaleId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("store_sale_products_store_sale_id_foreign");
    }
}