using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class OrderFixedPriceRowConfiguration : IEntityTypeConfiguration<OrderFixedPriceRow>
{
    public void Configure(EntityTypeBuilder<OrderFixedPriceRow> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint")
            .HasColumnName("id");

        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");

        entity.Property(e => e.Description)
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("description");

        entity.Property(e => e.HasRut)
            .HasColumnType("tinyint")
            .HasColumnName("has_rut");

        entity.Property(e => e.OrderFixedPriceId)
            .HasColumnType("bigint")
            .HasColumnName("order_fixed_price_id");

        entity.Property(e => e.Price)
            .HasColumnType("decimal(8,2)")
            .HasColumnName("price");

        entity.Property(e => e.Quantity)
            .HasColumnType("int")
            .HasColumnName("quantity");

        entity.Property(e => e.Type)
            .IsRequired()
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("type");

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

        entity.HasIndex(e => e.OrderFixedPriceId, "order_fixed_price_rows_order_fixed_price_id_foreign");

        entity.ToTable("order_fixed_price_rows").HasCharSet(DatabaseConstants.CharSets.Utf8mb4).UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.OrderFixedPrice)
            .WithMany(p => p.OrderFixedPriceRows)
            .HasForeignKey(d => d.OrderFixedPriceId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("order_fixed_price_rows_order_fixed_price_id_foreign");
    }
}