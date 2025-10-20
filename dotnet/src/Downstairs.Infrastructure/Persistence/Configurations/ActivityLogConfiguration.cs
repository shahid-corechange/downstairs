using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class ActivityLogConfiguration : IEntityTypeConfiguration<ActivityLog>
{
    public void Configure(EntityTypeBuilder<ActivityLog> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint")
            .HasColumnName("id");

        entity.Property(e => e.BatchUuid)
            .HasColumnType("char(36)")
            .HasColumnName("batch_uuid");

        entity.Property(e => e.CauserId)
            .HasColumnType("bigint")
            .HasColumnName("causer_id");

        entity.Property(e => e.CauserType)
            .HasColumnType("varchar(255)")
            .HasColumnName("causer_type");

        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");

        entity.Property(e => e.Description)
            .IsRequired()
            .HasColumnType("text")
            .HasColumnName("description");

        entity.Property(e => e.Event)
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("event");

        entity.Property(e => e.LogName)
            .HasColumnType("varchar(255)")
            .HasColumnName("log_name");

        entity.Property(e => e.Properties)
            .HasColumnType("json")
            .HasColumnName("properties");

        entity.Property(e => e.SubjectId)
            .HasColumnType("bigint")
            .HasColumnName("subject_id");

        entity.Property(e => e.SubjectType)
            .HasColumnType("varchar(255)")
            .HasColumnName("subject_type");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.CreatedAt, "activity_log_created_at_index");

        entity.HasIndex(e => e.LogName, "activity_log_log_name_index");

        entity.HasIndex(e => new { e.CauserType, e.CauserId }, "causer");

        entity.HasIndex(e => new { e.SubjectType, e.SubjectId }, "subject");

        entity.ToTable("activity_log").UseCollation(DatabaseConstants.Collations.Unicode);
    }
}