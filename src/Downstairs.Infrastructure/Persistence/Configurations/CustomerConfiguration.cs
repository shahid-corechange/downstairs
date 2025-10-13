using Downstairs.Domain.Entities;
using Downstairs.Domain.ValueObjects;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

/// <summary>
/// Entity Framework configuration for Customer entity
/// </summary>
public class CustomerConfiguration : IEntityTypeConfiguration<Customer>
{
    public void Configure(EntityTypeBuilder<Customer> builder)
    {
        builder.ToTable("customers");

        builder.HasKey(x => x.Id);

        builder.Property(x => x.Id)
            .HasColumnName("id");

        builder.Property(x => x.Name)
            .HasColumnName("name")
            .HasMaxLength(200)
            .IsRequired();

        builder.Property(x => x.Email)
            .HasColumnName("email")
            .HasMaxLength(255)
            .IsRequired();

        builder.Property(x => x.OrganizationNumber)
            .HasColumnName("organization_number")
            .HasMaxLength(50)
            .IsRequired();

        builder.HasIndex(x => x.OrganizationNumber)
            .IsUnique();

        builder.Property(x => x.Phone)
            .HasColumnName("phone")
            .HasMaxLength(50);

        // Configure Address as owned type
        builder.OwnsOne(x => x.Address, address =>
        {
            address.Property(a => a.Street)
                .HasColumnName("address_street")
                .HasMaxLength(200);

            address.Property(a => a.City)
                .HasColumnName("address_city")
                .HasMaxLength(100);

            address.Property(a => a.PostalCode)
                .HasColumnName("address_postal_code")
                .HasMaxLength(20);

            address.Property(a => a.Country)
                .HasColumnName("address_country")
                .HasMaxLength(100);
        });

        builder.Property(x => x.FortnoxCustomerNumber)
            .HasColumnName("fortnox_customer_number")
            .HasMaxLength(50);

        builder.Property(x => x.IsActive)
            .HasColumnName("is_active");

        builder.Property(x => x.CreatedAt)
            .HasColumnName("created_at");

        builder.Property(x => x.UpdatedAt)
            .HasColumnName("updated_at");

        // Configure relationships
        builder.HasMany(x => x.Invoices)
            .WithOne(i => i.Customer)
            .HasForeignKey(i => i.CustomerId)
            .OnDelete(DeleteBehavior.Cascade);

        // Ignore domain events
        builder.Ignore(x => x.DomainEvents);
    }
}