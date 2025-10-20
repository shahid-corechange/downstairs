using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class CustomTaskConfiguration : IEntityTypeConfiguration<CustomTask>
{
    public void Configure(EntityTypeBuilder<CustomTask> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint")
            .HasColumnName("id");

        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");

        entity.Property(e => e.TaskableId)
            .HasColumnType("bigint")
            .HasColumnName("taskable_id");

        entity.Property(e => e.TaskableType)
            .IsRequired()
            .HasColumnType("varchar(255)")
            .HasColumnName("taskable_type");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => new { e.TaskableType, e.TaskableId }, "taskable_index");

        entity.ToTable("custom_tasks").UseCollation(DatabaseConstants.Collations.Unicode);
    }
}