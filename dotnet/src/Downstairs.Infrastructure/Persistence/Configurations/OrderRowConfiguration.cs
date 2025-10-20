using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class OrderRowConfiguration : IEntityTypeConfiguration<OrderRow>
{
    public void Configure(EntityTypeBuilder<OrderRow> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint")
            .HasColumnName("id");

        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");

        entity.Property(e => e.Description)
            .HasColumnType("text")
            .HasColumnName("description");

        entity.Property(e => e.DiscountPercentage)
            .HasColumnType("tinyint")
            .HasColumnName("discount_percentage");

        entity.Property(e => e.FortnoxArticleId)
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("fortnox_article_id");

        entity.Property(e => e.HasRut)
            .HasColumnType("tinyint(1)")
            .HasColumnName("has_rut");

        entity.Property(e => e.InternalNote)
            .HasColumnType("text")
            .HasColumnName("internal_note");

        entity.Property(e => e.OrderId)
            .HasColumnType("bigint")
            .HasColumnName("order_id");

        entity.Property(e => e.Price)
            .HasPrecision(8, 2)
            .HasColumnType("decimal(8,2)")
            .HasColumnName("price");

        entity.Property(e => e.Quantity)
            .HasColumnType("decimal(8,2)")
            .HasColumnName("quantity");

        entity.Property(e => e.Unit)
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("unit");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.Property(e => e.Vat)
            .ValueGeneratedOnAdd()
            .HasColumnType("smallint")
            .HasColumnName("vat")
            .HasDefaultValueSql("'25'");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.OrderId, "order_rows_order_id_foreign");

        entity.ToTable("order_rows").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.Order)
            .WithMany(p => p.OrderRows)
            .HasForeignKey(d => d.OrderId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("order_rows_order_id_foreign");
    }
}