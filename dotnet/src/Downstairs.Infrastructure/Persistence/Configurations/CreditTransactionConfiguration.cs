using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class CreditTransactionConfiguration : IEntityTypeConfiguration<CreditTransaction>
{
    public void Configure(EntityTypeBuilder<CreditTransaction> entity)
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

        entity.Property(e => e.IssuerId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("issuer_id");

        entity.Property(e => e.ScheduleId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("schedule_id");

        entity.Property(e => e.TotalAmount)
            .HasColumnType("bigint unsigned")
            .HasColumnName("total_amount");

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

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.UserId, "credit_transactions_user_id_foreign");

        entity.HasIndex(e => e.IssuerId, "credit_transactions_issuer_id_foreign");

        entity.HasIndex(e => e.ScheduleId, "credit_transactions_schedule_id_foreign");

        entity.ToTable("credit_transactions").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.Issuer)
            .WithMany(p => p.CreditTransactionIssuers)
            .HasForeignKey(d => d.IssuerId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("credit_transactions_issuer_id_foreign");

        entity.HasOne(d => d.Schedule)
            .WithMany(p => p.CreditTransactions)
            .HasForeignKey(d => d.ScheduleId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("credit_transactions_schedule_id_foreign");

        entity.HasOne(d => d.User)
            .WithMany(p => p.CreditTransactionUsers)
            .HasForeignKey(d => d.UserId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("credit_transactions_user_id_foreign");
    }
}