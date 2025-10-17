using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class CountryConfiguration : IEntityTypeConfiguration<Country>
{
    public void Configure(EntityTypeBuilder<Country> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("countries")
            .UseCollation("utf8mb4_unicode_ci");

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.Code)
            .HasMaxLength(2)
            .IsFixedLength()
            .HasColumnName("code");
        entity.Property(e => e.Currency)
            .HasMaxLength(3)
            .IsFixedLength()
            .HasColumnName("currency");
        entity.Property(e => e.DialCode)
            .HasMaxLength(255)
            .HasColumnName("dial_code");
        entity.Property(e => e.Flag)
            .HasMaxLength(255)
            .HasColumnName("flag");
        entity.Property(e => e.Name)
            .HasMaxLength(255)
            .HasColumnName("name");
    }
}

