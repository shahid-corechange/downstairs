using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class CategoryableConfiguration : IEntityTypeConfiguration<Categoryable>
{
    public void Configure(EntityTypeBuilder<Categoryable> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint unsigned")
            .HasColumnName("id");

        entity.Property(e => e.CategoryId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("category_id");

        entity.Property(e => e.CategoryableId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("categoryable_id");

        entity.Property(e => e.CategoryableType)
            .IsRequired()
            .HasColumnType("varchar(255)")
            .HasColumnName("categoryable_type");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.CategoryId, "categoryables_category_id_foreign");

        entity.HasIndex(e => new { e.CategoryableType, e.CategoryableId }, "categoryables_categoryable_type_categoryable_id_index");

        entity.ToTable("categoryables").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.Category)
            .WithMany(p => p.Categoryables)
            .HasForeignKey(d => d.CategoryId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("categoryables_category_id_foreign");
    }
}