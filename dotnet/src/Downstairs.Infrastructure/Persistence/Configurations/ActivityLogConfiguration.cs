using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class ActivityLogConfiguration : IEntityTypeConfiguration<ActivityLog>
{
    public void Configure(EntityTypeBuilder<ActivityLog> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("activity_log")
            .UseCollation("utf8mb4_unicode_ci");

        entity.HasIndex(e => e.CreatedAt, "activity_log_created_at_index");

        entity.HasIndex(e => e.LogName, "activity_log_log_name_index");

        entity.HasIndex(e => new { e.CauserType, e.CauserId }, "causer");

        entity.HasIndex(e => new { e.SubjectType, e.SubjectId }, "subject");

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.BatchUuid).HasColumnName("batch_uuid");
        entity.Property(e => e.CauserId).HasColumnName("causer_id");
        entity.Property(e => e.CauserType).HasColumnName("causer_type");
        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");
        entity.Property(e => e.Description)
            .HasColumnType("text")
            .HasColumnName("description");
        entity.Property(e => e.Event)
            .HasMaxLength(255)
            .HasColumnName("event");
        entity.Property(e => e.LogName).HasColumnName("log_name");
        entity.Property(e => e.Properties)
            .HasColumnType("json")
            .HasColumnName("properties");
        entity.Property(e => e.SubjectId).HasColumnName("subject_id");
        entity.Property(e => e.SubjectType).HasColumnName("subject_type");
        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");
    }
}

