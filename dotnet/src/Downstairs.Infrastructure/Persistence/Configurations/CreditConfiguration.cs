using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class CreditConfiguration : IEntityTypeConfiguration<Credit>
{
    public void Configure(EntityTypeBuilder<Credit> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("credits")
            .UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasIndex(e => e.IssuerId, "credits_issuer_id_foreign");

        entity.HasIndex(e => e.ScheduleCleaningId, "credits_schedule_cleaning_id_foreign");

        entity.HasIndex(e => e.UserId, "credits_user_id_foreign");

        entity.HasIndex(e => e.ValidUntil, "credits_valid_until_index");

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");
        entity.Property(e => e.Description)
            .HasColumnType("text")
            .HasColumnName("description");
        entity.Property(e => e.InitialAmount).HasColumnName("initial_amount");
        entity.Property(e => e.IssuerId).HasColumnName("issuer_id");
        entity.Property(e => e.RemainingAmount).HasColumnName("remaining_amount");
        entity.Property(e => e.ScheduleCleaningId).HasColumnName("schedule_cleaning_id");
        entity.Property(e => e.Type)
            .HasMaxLength(255)
            .HasColumnName("type");
        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");
        entity.Property(e => e.UserId).HasColumnName("user_id");
        entity.Property(e => e.ValidUntil)
            .HasColumnType("timestamp")
            .HasColumnName("valid_until");

        entity.HasOne(d => d.Issuer).WithMany(p => p.CreditIssuers)
            .HasForeignKey(d => d.IssuerId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("credits_issuer_id_foreign");

        entity.HasOne(d => d.ScheduleCleaning).WithMany(p => p.Credits)
            .HasForeignKey(d => d.ScheduleCleaningId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("credits_schedule_cleaning_id_foreign");

        entity.HasOne(d => d.User).WithMany(p => p.CreditUsers)
            .HasForeignKey(d => d.UserId)
            .HasConstraintName("credits_user_id_foreign");
    }
}