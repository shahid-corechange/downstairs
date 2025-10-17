using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class DeviationConfiguration : IEntityTypeConfiguration<Deviation>
{
    public void Configure(EntityTypeBuilder<Deviation> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("deviations")
            .UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasIndex(e => e.ScheduleCleaningId, "deviations_schedule_cleaning_id_foreign");

        entity.HasIndex(e => e.Type, "deviations_type_index");

        entity.HasIndex(e => e.UserId, "deviations_user_id_foreign");

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");
        entity.Property(e => e.DeletedAt)
            .HasColumnType("timestamp")
            .HasColumnName("deleted_at");
        entity.Property(e => e.IsHandled).HasColumnName("is_handled");
        entity.Property(e => e.Reason)
            .HasColumnType("text")
            .HasColumnName("reason");
        entity.Property(e => e.ScheduleCleaningId).HasColumnName("schedule_cleaning_id");
        entity.Property(e => e.Type).HasColumnName("type");
        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");
        entity.Property(e => e.UserId).HasColumnName("user_id");

        entity.HasOne(d => d.ScheduleCleaning).WithMany(p => p.Deviations)
            .HasForeignKey(d => d.ScheduleCleaningId)
            .HasConstraintName("deviations_schedule_cleaning_id_foreign");

        entity.HasOne(d => d.User).WithMany(p => p.Deviations)
            .HasForeignKey(d => d.UserId)
            .HasConstraintName("deviations_user_id_foreign");
    }
}