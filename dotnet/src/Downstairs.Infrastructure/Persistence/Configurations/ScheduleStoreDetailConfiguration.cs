using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class ScheduleStoreDetailConfiguration : IEntityTypeConfiguration<ScheduleStoreDetail>
{
    public void Configure(EntityTypeBuilder<ScheduleStoreDetail> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("schedule_store_details")
            .UseCollation("utf8mb4_unicode_ci");

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.BeginsAtChanged)
            .HasColumnType("datetime")
            .HasColumnName("begins_at_changed");
        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");
        entity.Property(e => e.DeletedAt)
            .HasColumnType("timestamp")
            .HasColumnName("deleted_at");
        entity.Property(e => e.EndsAtChanged)
            .HasColumnType("datetime")
            .HasColumnName("ends_at_changed");
        entity.Property(e => e.ScheduleStoreId).HasColumnName("schedule_store_id");
        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");
    }
}

