using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class TranslationConfiguration : IEntityTypeConfiguration<Translation>
{
    public void Configure(EntityTypeBuilder<Translation> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("translations")
            .UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasIndex(e => new { e.TranslationableType, e.TranslationableId }, "translationable_index");

        entity.HasIndex(e => e.Key, "translations_key_index");

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");
        entity.Property(e => e.DeletedAt)
            .HasColumnType("timestamp")
            .HasColumnName("deleted_at");
        entity.Property(e => e.EnUs)
            .HasColumnType("text")
            .HasColumnName("en_US");
        entity.Property(e => e.Key).HasColumnName("key");
        entity.Property(e => e.NnNo)
            .HasColumnType("text")
            .HasColumnName("nn_NO");
        entity.Property(e => e.SvSe)
            .HasColumnType("text")
            .HasColumnName("sv_SE");
        entity.Property(e => e.TranslationableId).HasColumnName("translationable_id");
        entity.Property(e => e.TranslationableType).HasColumnName("translationable_type");
        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");
    }
}