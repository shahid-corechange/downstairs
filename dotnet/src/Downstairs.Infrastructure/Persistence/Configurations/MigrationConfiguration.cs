using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class MigrationConfiguration : IEntityTypeConfiguration<Migration>
{
    public void Configure(EntityTypeBuilder<Migration> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("migrations")
            .UseCollation("utf8mb4_unicode_ci");

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.Batch).HasColumnName("batch");
        entity.Property(e => e.Migration1)
            .HasMaxLength(255)
            .HasColumnName("migration");
    }
}

