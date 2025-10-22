using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class CreditCreditTransactionConfiguration : IEntityTypeConfiguration<CreditCreditTransaction>
{
    public void Configure(EntityTypeBuilder<CreditCreditTransaction> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint unsigned")
            .HasColumnName("id");

        entity.Property(e => e.Amount)
            .HasColumnType("tinyint unsigned")
            .HasColumnName("amount");

        entity.Property(e => e.CreditId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("credit_id");

        entity.Property(e => e.CreditTransactionId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("credit_transaction_id");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.CreditId, "credit_credit_transaction_credit_id_foreign");

        entity.HasIndex(e => e.CreditTransactionId, "credit_credit_transaction_credit_transaction_id_foreign");

        entity.ToTable("credit_credit_transaction").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.Credit)
            .WithMany(p => p.CreditCreditTransactions)
            .HasForeignKey(d => d.CreditId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("credit_credit_transaction_credit_id_foreign");

        entity.HasOne(d => d.CreditTransaction)
            .WithMany(p => p.CreditCreditTransactions)
            .HasForeignKey(d => d.CreditTransactionId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("credit_credit_transaction_credit_transaction_id_foreign");
    }
}