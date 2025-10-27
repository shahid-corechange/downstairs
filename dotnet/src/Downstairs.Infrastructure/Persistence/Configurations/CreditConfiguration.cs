using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class CreditConfiguration : IEntityTypeConfiguration<Credit>
{
    public void Configure(EntityTypeBuilder<Credit> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint unsigned")
            .HasColumnName("id");

        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");

        entity.Property(e => e.Description)
            .IsRequired()
            .HasColumnType("text")
            .HasColumnName("description");

        entity.Property(e => e.InitialAmount)
            .HasColumnType("tinyint unsigned")
            .HasColumnName("initial_amount");

        entity.Property(e => e.IssuerId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("issuer_id");

        entity.Property(e => e.RemainingAmount)
            .HasColumnType("tinyint unsigned")
            .HasColumnName("remaining_amount");

        entity.Property(e => e.ScheduleId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("schedule_id");

        entity.Property(e => e.Type)
            .IsRequired()
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("type");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.Property(e => e.UserId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("user_id");

        entity.Property(e => e.ValidUntil)
            .HasColumnType("timestamp")
            .HasColumnName("valid_until");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.UserId, "credits_user_id_foreign");

        entity.HasIndex(e => e.IssuerId, "credits_issuer_id_foreign");

        entity.HasIndex(e => e.ValidUntil, "credits_valid_until_index");

        entity.HasIndex(e => e.ScheduleId, "credits_schedule_id_foreign");

        entity.ToTable("credits").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.Issuer)
            .WithMany(p => p.CreditIssuers)
            .HasForeignKey(d => d.IssuerId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("credits_issuer_id_foreign");

        entity.HasOne(d => d.Schedule)
            .WithMany(p => p.Credits)
            .HasForeignKey(d => d.ScheduleId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("credits_schedule_id_foreign");

        entity.HasOne(d => d.User)
            .WithMany(p => p.CreditUsers)
            .HasForeignKey(d => d.UserId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("credits_user_id_foreign");
    }
}