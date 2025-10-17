using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class ScheduleCleaningDeviationConfiguration : IEntityTypeConfiguration<ScheduleCleaningDeviation>
{
    public void Configure(EntityTypeBuilder<ScheduleCleaningDeviation> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("schedule_cleaning_deviations")
            .UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasIndex(e => e.ScheduleCleaningId, "schedule_cleaning_deviations_schedule_cleaning_id_foreign");

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");
        entity.Property(e => e.DeletedAt)
            .HasColumnType("timestamp")
            .HasColumnName("deleted_at");
        entity.Property(e => e.IsHandled).HasColumnName("is_handled");
        entity.Property(e => e.Meta)
            .HasColumnType("json")
            .HasColumnName("meta");
        entity.Property(e => e.ScheduleCleaningId).HasColumnName("schedule_cleaning_id");
        entity.Property(e => e.Types)
            .HasColumnType("json")
            .HasColumnName("types");
        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.HasOne(d => d.ScheduleCleaning).WithMany(p => p.ScheduleCleaningDeviations)
            .HasForeignKey(d => d.ScheduleCleaningId)
            .HasConstraintName("schedule_cleaning_deviations_schedule_cleaning_id_foreign");
    }
}