using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class FixedPriceRowConfiguration : IEntityTypeConfiguration<FixedPriceRow>
{
    public void Configure(EntityTypeBuilder<FixedPriceRow> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint")
            .HasColumnName("id");

        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");

        entity.Property(e => e.FixedPriceId)
            .HasColumnType("bigint")
            .HasColumnName("fixed_price_id");

        entity.Property(e => e.HasRut)
            .HasColumnType("tinyint(1)")
            .HasColumnName("has_rut");

        entity.Property(e => e.Price)
            .HasColumnType("decimal(8,2)")
            .HasColumnName("price");

        entity.Property(e => e.Quantity)
            .HasColumnType("int")
            .HasColumnName("quantity");

        entity.Property(e => e.Type)
            .IsRequired()
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

        entity.HasIndex(e => e.FixedPriceId, "fixed_price_rows_fixed_price_id_foreign");

        entity.HasIndex(e => e.Type, "fixed_price_rows_type_index");

        entity.ToTable("fixed_price_rows").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.FixedPrice)
            .WithMany(p => p.FixedPriceRows)
            .HasForeignKey(d => d.FixedPriceId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("fixed_price_rows_fixed_price_id_foreign");
    }
}