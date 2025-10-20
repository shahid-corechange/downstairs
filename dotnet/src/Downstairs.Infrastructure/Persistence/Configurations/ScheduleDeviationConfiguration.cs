using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class ScheduleDeviationConfiguration : IEntityTypeConfiguration<ScheduleDeviation>
{
    public void Configure(EntityTypeBuilder<ScheduleDeviation> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint")
            .HasColumnName("id");

        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");

        entity.Property(e => e.DeletedAt)
            .HasColumnType("timestamp")
            .HasColumnName("deleted_at");

        entity.Property(e => e.IsHandled)
            .HasColumnType("tinyint(1)")
            .HasColumnName("is_handled");

        entity.Property(e => e.Meta)
            .HasColumnType("json")
            .HasColumnName("meta");

        entity.Property(e => e.ScheduleId)
            .HasColumnType("bigint")
            .HasColumnName("schedule_id");

        entity.Property(e => e.Types)
            .IsRequired()
            .HasColumnType("json")
            .HasColumnName("types");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.ScheduleId, "schedule_deviations_schedule_id_foreign");

        entity.ToTable("schedule_deviations").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.Schedule)
            .WithMany(p => p.ScheduleDeviations)
            .HasForeignKey(d => d.ScheduleId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("schedule_deviations_schedule_id_foreign");
    }
}