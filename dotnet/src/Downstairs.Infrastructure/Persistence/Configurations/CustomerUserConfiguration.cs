using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class CustomerUserConfiguration : IEntityTypeConfiguration<CustomerUser>
{
    public void Configure(EntityTypeBuilder<CustomerUser> entity)
    {
        entity.Property(e => e.MyRowId)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint")
            .HasColumnName("my_row_id");

        entity.Property(e => e.CustomerId)
            .HasColumnType("bigint")
            .HasColumnName("customer_id");

        entity.Property(e => e.UserId)
            .HasColumnType("bigint")
            .HasColumnName("user_id");

        entity.HasKey(e => e.MyRowId)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.CustomerId, "customer_user_customer_id_foreign");

        entity.HasIndex(e => e.UserId, "customer_user_user_id_foreign");

        entity.ToTable("customer_user").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.Customer)
            .WithMany(p => p.CustomerUsers)
            .HasForeignKey(d => d.CustomerId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("customer_user_customer_id_foreign");

        entity.HasOne(d => d.User)
            .WithMany(p => p.CustomerUsers)
            .HasForeignKey(d => d.UserId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("customer_user_user_id_foreign");
    }
}