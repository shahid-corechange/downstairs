using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class CustomerConfiguration : IEntityTypeConfiguration<Customer>
{
    public void Configure(EntityTypeBuilder<Customer> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint")
            .HasColumnName("id");

        entity.Property(e => e.AddressId)
            .HasColumnType("bigint")
            .HasColumnName("address_id");

        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");

        entity.Property(e => e.CustomerRefId)
            .HasColumnType("bigint")
            .HasColumnName("customer_ref_id");

        entity.Property(e => e.DeletedAt)
            .HasColumnType("timestamp")
            .HasColumnName("deleted_at");

        entity.Property(e => e.DialCode)
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("dial_code");

        entity.Property(e => e.DueDays)
            .ValueGeneratedOnAdd()
            .HasColumnType("smallint")
            .HasColumnName("due_days")
            .HasDefaultValueSql("'30'");

        entity.Property(e => e.Email)
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("email");

        entity.Property(e => e.FortnoxId)
            .HasColumnType("text")
            .HasColumnName("fortnox_id");

        entity.Property(e => e.IdentityNumber)
            .IsRequired()
            .HasColumnType("text")
            .HasColumnName("identity_number");

        entity.Property(e => e.InvoiceMethod)
            .IsRequired()
            .ValueGeneratedOnAdd()
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("invoice_method")
            .HasDefaultValueSql("'print'");

        entity.Property(e => e.MembershipType)
            .IsRequired()
            .ValueGeneratedOnAdd()
            .HasColumnType("varchar(255)")
            .HasColumnName("membership_type")
            .HasDefaultValueSql("'private'");

        entity.Property(e => e.Name)
            .IsRequired()
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("name");

        entity.Property(e => e.Phone1)
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("phone1");

        entity.Property(e => e.Reference)
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("reference");

        entity.Property(e => e.Type)
            .IsRequired()
            .ValueGeneratedOnAdd()
            .HasColumnType("varchar(255)")
            .HasColumnName("type")
            .HasDefaultValueSql("'primary'");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.AddressId, "customers_address_id_foreign");

        entity.HasIndex(e => e.CustomerRefId, "customers_customer_ref_id_foreign");

        entity.HasIndex(e => e.MembershipType, "customers_membership_type_index");

        entity.HasIndex(e => e.Type, "customers_type_index");

        entity.ToTable("customers").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.Address)
            .WithMany(p => p.Customers)
            .HasForeignKey(d => d.AddressId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("customers_address_id_foreign");

        entity.HasOne(d => d.CustomerRef)
            .WithMany(p => p.InverseCustomerRef)
            .HasForeignKey(d => d.CustomerRefId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("customers_customer_ref_id_foreign");
    }
}