using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class OldCustomerConfiguration : IEntityTypeConfiguration<OldCustomer>
{
    public void Configure(EntityTypeBuilder<OldCustomer> entity)
    {
        entity.HasKey(e => e.MyRowId).HasName("PRIMARY");

        entity
            .ToTable("old_customers")
            .UseCollation("utf8mb4_unicode_ci");

        entity.HasIndex(e => e.CustomerId, "old_customers_customer_id_foreign");

        entity.Property(e => e.MyRowId).HasColumnName("my_row_id");
        entity.Property(e => e.CustomerId).HasColumnName("customer_id");
        entity.Property(e => e.OldCustomerId).HasColumnName("old_customer_id");

        entity.HasOne(d => d.Customer).WithMany(p => p.OldCustomers)
            .HasForeignKey(d => d.CustomerId)
            .HasConstraintName("old_customers_customer_id_foreign");
    }
}

