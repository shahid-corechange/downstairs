using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class OrderRowConfiguration : IEntityTypeConfiguration<OrderRow>
{
    public void Configure(EntityTypeBuilder<OrderRow> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("order_rows")
            .UseCollation("utf8mb4_unicode_ci");

        entity.HasIndex(e => e.OrderId, "order_rows_order_id_foreign");

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");
        entity.Property(e => e.Description)
            .HasColumnType("text")
            .HasColumnName("description");
        entity.Property(e => e.DiscountPercentage).HasColumnName("discount_percentage");
        entity.Property(e => e.FortnoxArticleId)
            .HasMaxLength(255)
            .HasColumnName("fortnox_article_id");
        entity.Property(e => e.HasRut).HasColumnName("has_rut");
        entity.Property(e => e.InternalNote)
            .HasColumnType("text")
            .HasColumnName("internal_note");
        entity.Property(e => e.OrderId).HasColumnName("order_id");
        entity.Property(e => e.Price)
            .HasColumnType("decimal(8,2) unsigned")
            .HasColumnName("price");
        entity.Property(e => e.Quantity)
            .HasColumnType("decimal(8,2) unsigned")
            .HasColumnName("quantity");
        entity.Property(e => e.Unit)
            .HasMaxLength(255)
            .HasColumnName("unit");
        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");
        entity.Property(e => e.Vat)
            .HasDefaultValueSql("'25'")
            .HasColumnName("vat");

        entity.HasOne(d => d.Order).WithMany(p => p.OrderRows)
            .HasForeignKey(d => d.OrderId)
            .HasConstraintName("order_rows_order_id_foreign");
    }
}

