using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class CustomTaskConfiguration : IEntityTypeConfiguration<CustomTask>
{
    public void Configure(EntityTypeBuilder<CustomTask> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("custom_tasks")
            .UseCollation("utf8mb4_unicode_ci");

        entity.HasIndex(e => new { e.TaskableType, e.TaskableId }, "taskable_index");

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");
        entity.Property(e => e.TaskableId).HasColumnName("taskable_id");
        entity.Property(e => e.TaskableType).HasColumnName("taskable_type");
        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");
    }
}

