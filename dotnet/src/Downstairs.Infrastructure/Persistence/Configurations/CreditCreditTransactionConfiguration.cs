using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class CreditCreditTransactionConfiguration : IEntityTypeConfiguration<CreditCreditTransaction>
{
    public void Configure(EntityTypeBuilder<CreditCreditTransaction> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("credit_credit_transaction")
            .UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasIndex(e => e.CreditId, "credit_credit_transaction_credit_id_foreign");

        entity.HasIndex(e => e.CreditTransactionId, "credit_credit_transaction_credit_transaction_id_foreign");

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.Amount).HasColumnName("amount");
        entity.Property(e => e.CreditId).HasColumnName("credit_id");
        entity.Property(e => e.CreditTransactionId).HasColumnName("credit_transaction_id");

        entity.HasOne(d => d.Credit).WithMany(p => p.CreditCreditTransactions)
            .HasForeignKey(d => d.CreditId)
            .HasConstraintName("credit_credit_transaction_credit_id_foreign");

        entity.HasOne(d => d.CreditTransaction).WithMany(p => p.CreditCreditTransactions)
            .HasForeignKey(d => d.CreditTransactionId)
            .HasConstraintName("credit_credit_transaction_credit_transaction_id_foreign");
    }
}