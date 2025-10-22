using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class PriceAdjustmentConfiguration : IEntityTypeConfiguration<PriceAdjustment>
{
    public void Configure(EntityTypeBuilder<PriceAdjustment> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint unsigned")
            .HasColumnName("id");

        entity.Property(e => e.CauserId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("causer_id");

        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");

        entity.Property(e => e.DeletedAt)
            .HasColumnType("timestamp")
            .HasColumnName("deleted_at");

        entity.Property(e => e.Description)
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("description");

        entity.Property(e => e.ExecutionDate)
            .HasColumnType("date")
            .HasColumnName("execution_date");

        entity.Property(e => e.Price)
            .HasPrecision(8, 2)
            .HasColumnType("decimal(8,2) unsigned")
            .HasColumnName("price");

        entity.Property(e => e.PriceType)
            .IsRequired()
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("price_type");

        entity.Property(e => e.Status)
            .IsRequired()
            .ValueGeneratedOnAdd()
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("status")
            .HasDefaultValueSql("'pending'");

        entity.Property(e => e.Type)
            .IsRequired()
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("type");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.CauserId, "price_adjustments_causer_id_foreign");

        entity.ToTable("price_adjustments").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.Causer)
            .WithMany(p => p.PriceAdjustments)
            .HasForeignKey(d => d.CauserId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("price_adjustments_causer_id_foreign");
    }
}