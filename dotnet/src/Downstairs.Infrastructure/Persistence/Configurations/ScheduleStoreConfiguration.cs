using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class ScheduleStoreConfiguration : IEntityTypeConfiguration<ScheduleStore>
{
    public void Configure(EntityTypeBuilder<ScheduleStore> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("schedule_stores")
            .UseCollation("utf8mb4_unicode_ci");

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.ContactId).HasColumnName("contact_id");
        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");
        entity.Property(e => e.DeletedAt)
            .HasColumnType("timestamp")
            .HasColumnName("deleted_at");
        entity.Property(e => e.DistrictId).HasColumnName("district_id");
        entity.Property(e => e.EndAt)
            .HasColumnType("timestamp")
            .HasColumnName("end_at");
        entity.Property(e => e.StartAt)
            .HasColumnType("timestamp")
            .HasColumnName("start_at");
        entity.Property(e => e.Status)
            .HasMaxLength(255)
            .HasDefaultValueSql("'draft'")
            .HasColumnName("status");
        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");
        entity.Property(e => e.UserId).HasColumnName("user_id");
    }
}

