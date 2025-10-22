using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class ProductableConfiguration : IEntityTypeConfiguration<Productable>
{
    public void Configure(EntityTypeBuilder<Productable> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint unsigned")
            .HasColumnName("id");

        entity.Property(e => e.ProductId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("product_id");

        entity.Property(e => e.ProductableId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("productable_id");

        entity.Property(e => e.ProductableType)
            .IsRequired()
            .HasColumnType("varchar(255)")
            .HasColumnName("productable_type");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.ProductId, "productables_product_id_foreign");

        entity.HasIndex(e => new { e.ProductableType, e.ProductableId }, "productables_productable_type_productable_id_index");

        entity.ToTable("productables").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.Product)
            .WithMany(p => p.Productables)
            .HasForeignKey(d => d.ProductId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("productables_product_id_foreign");
    }
}