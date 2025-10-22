using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class FailedJobConfiguration : IEntityTypeConfiguration<FailedJob>
{
    public void Configure(EntityTypeBuilder<FailedJob> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint unsigned")
            .HasColumnName("id");

        entity.Property(e => e.Connection)
            .IsRequired()
            .HasColumnType("text")
            .HasColumnName("connection");

        entity.Property(e => e.Exception)
            .IsRequired()
            .HasColumnType("longtext")
            .HasColumnName("exception");

        entity.Property(e => e.FailedAt)
            .ValueGeneratedOnAdd()
            .HasColumnType("timestamp")
            .HasColumnName("failed_at")
            .HasDefaultValueSql("CURRENT_TIMESTAMP");

        entity.Property(e => e.Payload)
            .IsRequired()
            .HasColumnType("longtext")
            .HasColumnName("payload");

        entity.Property(e => e.Queue)
            .IsRequired()
            .HasColumnType("text")
            .HasColumnName("queue");

        entity.Property(e => e.Uuid)
            .IsRequired()
            .HasColumnType("varchar(255)")
            .HasColumnName("uuid");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.Uuid, "failed_jobs_uuid_unique")
            .IsUnique();

        entity.ToTable("failed_jobs").UseCollation(DatabaseConstants.Collations.Unicode);
    }
}