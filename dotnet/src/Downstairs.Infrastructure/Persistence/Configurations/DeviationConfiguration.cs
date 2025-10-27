using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class DeviationConfiguration : IEntityTypeConfiguration<Deviation>
{
    public void Configure(EntityTypeBuilder<Deviation> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint unsigned")
            .HasColumnName("id");

        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");

        entity.Property(e => e.DeletedAt)
            .HasColumnType("timestamp")
            .HasColumnName("deleted_at");

        entity.Property(e => e.IsHandled)
            .HasColumnType("tinyint(1)")
            .HasDefaultValueSql("'0'")
            .HasColumnName("is_handled");

        entity.Property(e => e.Reason)
            .HasColumnType("text")
            .HasColumnName("reason");

        entity.Property(e => e.ScheduleId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("schedule_id");

        entity.Property(e => e.Type)
            .IsRequired()
            .HasColumnType("varchar(255)")
            .HasColumnName("type");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.Property(e => e.UserId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("user_id");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.UserId, "deviations_user_id_foreign");

        entity.HasIndex(e => e.Type, "deviations_type_index");

        entity.HasIndex(e => e.ScheduleId, "deviations_schedule_id_foreign");

        entity.ToTable("deviations").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.Schedule)
            .WithMany(p => p.Deviations)
            .HasForeignKey(d => d.ScheduleId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("deviations_schedule_id_foreign");

        entity.HasOne(d => d.User)
            .WithMany(p => p.Deviations)
            .HasForeignKey(d => d.UserId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("deviations_user_id_foreign");
    }
}