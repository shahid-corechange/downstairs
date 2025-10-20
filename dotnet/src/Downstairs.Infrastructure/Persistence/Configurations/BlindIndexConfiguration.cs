using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class BlindIndexConfiguration : IEntityTypeConfiguration<BlindIndex>
{
    public void Configure(EntityTypeBuilder<BlindIndex> entity)
    {
        entity.Property(e => e.MyRowId)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint")
            .HasColumnName("my_row_id");

        entity.Property(e => e.IndexableId)
            .HasColumnType("bigint")
            .HasColumnName("indexable_id");

        entity.Property(e => e.IndexableType)
            .IsRequired()
            .HasColumnType("varchar(255)")
            .HasColumnName("indexable_type");

        entity.Property(e => e.Name)
            .IsRequired()
            .HasColumnType("varchar(255)")
            .HasColumnName("name");

        entity.Property(e => e.Value)
            .IsRequired()
            .HasColumnType("varchar(255)")
            .HasColumnName("value");

        entity.HasKey(e => e.MyRowId)
            .HasName("PRIMARY");

        entity.HasIndex(e => new { e.IndexableType, e.IndexableId }, "blind_indexes_indexable_type_indexable_id_index");

        entity.HasIndex(e => new { e.IndexableType, e.IndexableId, e.Name }, "blind_indexes_indexable_type_indexable_id_name_unique")
            .IsUnique();

        entity.HasIndex(e => new { e.Name, e.Value }, "blind_indexes_name_value_index");

        entity.ToTable("blind_indexes").UseCollation(DatabaseConstants.Collations.Unicode);
    }
}