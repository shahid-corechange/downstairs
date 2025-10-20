using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class CountryConfiguration : IEntityTypeConfiguration<Country>
{
    public void Configure(EntityTypeBuilder<Country> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint")
            .HasColumnName("id");

        entity.Property(e => e.Code)
            .IsRequired()
            .HasMaxLength(2)
            .HasColumnType("char(2)")
            .HasColumnName("code")
            .IsFixedLength();

        entity.Property(e => e.Currency)
            .IsRequired()
            .HasMaxLength(3)
            .HasColumnType("char(3)")
            .HasColumnName("currency")
            .IsFixedLength();

        entity.Property(e => e.DialCode)
            .IsRequired()
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("dial_code");

        entity.Property(e => e.Flag)
            .IsRequired()
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("flag");

        entity.Property(e => e.Name)
            .IsRequired()
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("name");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.ToTable("countries").UseCollation(DatabaseConstants.Collations.Unicode);
    }
}