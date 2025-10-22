using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class LaundryPreferenceConfiguration : IEntityTypeConfiguration<LaundryPreference>
{
    public void Configure(EntityTypeBuilder<LaundryPreference> entity)
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

        entity.Property(e => e.Hours)
            .HasColumnType("smallint unsigned")
            .HasColumnName("hours");

        entity.Property(e => e.IncludeHolidays)
            .HasColumnType("tinyint(1)")
            .HasDefaultValueSql("'0'")
            .HasColumnName("include_holidays");

        entity.Property(e => e.Percentage)
            .HasColumnType("decimal(8,2) unsigned")
            .HasDefaultValueSql("'0.00'")
            .HasColumnName("percentage");

        entity.Property(e => e.Price)
            .HasColumnType("decimal(8,2) unsigned")
            .HasDefaultValueSql("'0.00'")
            .HasColumnName("price");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.Property(e => e.VatGroup)
            .ValueGeneratedOnAdd()
            .HasColumnType("tinyint unsigned")
            .HasColumnName("vat_group")
            .HasDefaultValueSql("'25'");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.ToTable("laundry_preferences").UseCollation(DatabaseConstants.Collations.Unicode);
    }
}