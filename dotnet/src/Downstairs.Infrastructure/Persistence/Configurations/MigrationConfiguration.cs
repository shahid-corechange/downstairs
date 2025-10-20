using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class MigrationConfiguration : IEntityTypeConfiguration<Migration>
{
    public void Configure(EntityTypeBuilder<Migration> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("int")
            .HasColumnName("id");

        entity.Property(e => e.Batch)
            .HasColumnType("int")
            .HasColumnName("batch");

        entity.Property(e => e.Migration1)
            .IsRequired()
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("migration");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.ToTable("migrations").UseCollation(DatabaseConstants.Collations.Unicode);
    }
}