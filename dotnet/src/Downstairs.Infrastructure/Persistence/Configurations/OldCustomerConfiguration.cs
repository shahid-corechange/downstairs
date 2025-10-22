using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class OldCustomerConfiguration : IEntityTypeConfiguration<OldCustomer>
{
    public void Configure(EntityTypeBuilder<OldCustomer> entity)
    {
        entity.Property(e => e.MyRowId)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint unsigned")
            .HasColumnName("my_row_id");

        entity.Property(e => e.CustomerId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("customer_id");

        entity.Property(e => e.OldCustomerId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("old_customer_id");

        entity.HasKey(e => e.MyRowId)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.CustomerId, "old_customers_customer_id_foreign");

        entity.ToTable("old_customers").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.Customer)
            .WithMany(p => p.OldCustomers)
            .HasForeignKey(d => d.CustomerId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("old_customers_customer_id_foreign");
    }
}