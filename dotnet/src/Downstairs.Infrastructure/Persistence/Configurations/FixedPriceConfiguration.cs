using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class FixedPriceConfiguration : IEntityTypeConfiguration<FixedPrice>
{
    public void Configure(EntityTypeBuilder<FixedPrice> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint unsigned")
            .HasColumnName("id");

        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");

        entity.Property(e => e.DeletedAt)
            .HasColumnType("timestamp")
            .HasColumnName("deleted_at");

        entity.Property(e => e.EndDate)
            .HasColumnType("date")
            .HasColumnName("end_date");

        entity.Property(e => e.IsPerOrder)
            .HasColumnType("tinyint(1)")
            .HasDefaultValueSql("'0'")
            .HasColumnName("is_per_order");

        entity.Property(e => e.StartDate)
            .HasColumnType("date")
            .HasColumnName("start_date");

        entity.Property(e => e.Type)
            .IsRequired()
            .ValueGeneratedOnAdd()
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("type")
            .HasDefaultValueSql("'cleaning'");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.Property(e => e.UserId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("user_id");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.UserId, "fixed_prices_user_id_foreign");

        entity.HasIndex(e => e.CreatedAt, "fixed_prices_created_at_index");

        entity.ToTable("fixed_prices").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.User)
            .WithMany(p => p.FixedPrices)
            .HasForeignKey(d => d.UserId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("fixed_prices_user_id_foreign");
    }
}