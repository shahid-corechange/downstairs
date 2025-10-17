using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class CreditTransactionConfiguration : IEntityTypeConfiguration<CreditTransaction>
{
    public void Configure(EntityTypeBuilder<CreditTransaction> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("credit_transactions")
            .UseCollation("utf8mb4_unicode_ci");

        entity.HasIndex(e => e.IssuerId, "credit_transactions_issuer_id_foreign");

        entity.HasIndex(e => e.ScheduleCleaningId, "credit_transactions_schedule_cleaning_id_foreign");

        entity.HasIndex(e => e.UserId, "credit_transactions_user_id_foreign");

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");
        entity.Property(e => e.Description)
            .HasColumnType("text")
            .HasColumnName("description");
        entity.Property(e => e.IssuerId).HasColumnName("issuer_id");
        entity.Property(e => e.ScheduleCleaningId).HasColumnName("schedule_cleaning_id");
        entity.Property(e => e.TotalAmount).HasColumnName("total_amount");
        entity.Property(e => e.Type)
            .HasMaxLength(255)
            .HasColumnName("type");
        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");
        entity.Property(e => e.UserId).HasColumnName("user_id");

        entity.HasOne(d => d.Issuer).WithMany(p => p.CreditTransactionIssuers)
            .HasForeignKey(d => d.IssuerId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("credit_transactions_issuer_id_foreign");

        entity.HasOne(d => d.ScheduleCleaning).WithMany(p => p.CreditTransactions)
            .HasForeignKey(d => d.ScheduleCleaningId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("credit_transactions_schedule_cleaning_id_foreign");

        entity.HasOne(d => d.User).WithMany(p => p.CreditTransactionUsers)
            .HasForeignKey(d => d.UserId)
            .HasConstraintName("credit_transactions_user_id_foreign");
    }
}

