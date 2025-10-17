using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class CustomerUserConfiguration : IEntityTypeConfiguration<CustomerUser>
{
    public void Configure(EntityTypeBuilder<CustomerUser> entity)
    {
        entity.HasKey(e => e.MyRowId).HasName("PRIMARY");

        entity
            .ToTable("customer_user")
            .UseCollation("utf8mb4_unicode_ci");

        entity.HasIndex(e => e.CustomerId, "customer_user_customer_id_foreign");

        entity.HasIndex(e => e.UserId, "customer_user_user_id_foreign");

        entity.Property(e => e.MyRowId).HasColumnName("my_row_id");
        entity.Property(e => e.CustomerId).HasColumnName("customer_id");
        entity.Property(e => e.UserId).HasColumnName("user_id");

        entity.HasOne(d => d.Customer).WithMany(p => p.CustomerUsers)
            .HasForeignKey(d => d.CustomerId)
            .HasConstraintName("customer_user_customer_id_foreign");

        entity.HasOne(d => d.User).WithMany(p => p.CustomerUsers)
            .HasForeignKey(d => d.UserId)
            .HasConstraintName("customer_user_user_id_foreign");
    }
}

