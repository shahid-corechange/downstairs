using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class FailedJobConfiguration : IEntityTypeConfiguration<FailedJob>
{
    public void Configure(EntityTypeBuilder<FailedJob> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("failed_jobs")
            .UseCollation("utf8mb4_unicode_ci");

        entity.HasIndex(e => e.Uuid, "failed_jobs_uuid_unique").IsUnique();

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.Connection)
            .HasColumnType("text")
            .HasColumnName("connection");
        entity.Property(e => e.Exception).HasColumnName("exception");
        entity.Property(e => e.FailedAt)
            .HasDefaultValueSql("CURRENT_TIMESTAMP")
            .HasColumnType("timestamp")
            .HasColumnName("failed_at");
        entity.Property(e => e.Payload).HasColumnName("payload");
        entity.Property(e => e.Queue)
            .HasColumnType("text")
            .HasColumnName("queue");
        entity.Property(e => e.Uuid).HasColumnName("uuid");
    }
}

