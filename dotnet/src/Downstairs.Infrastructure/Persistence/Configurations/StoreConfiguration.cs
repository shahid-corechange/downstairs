using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class StoreConfiguration : IEntityTypeConfiguration<Store>
{
    public void Configure(EntityTypeBuilder<Store> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint")
            .HasColumnName("id");

        entity.Property(e => e.AddressId)
            .HasColumnType("bigint")
            .HasColumnName("address_id");

        entity.Property(e => e.CompanyNumber)
            .IsRequired()
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("company_number");

        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");

        entity.Property(e => e.DeletedAt)
            .HasColumnType("timestamp")
            .HasColumnName("deleted_at");

        entity.Property(e => e.DialCode)
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("dial_code");

        entity.Property(e => e.Email)
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("email");

        entity.Property(e => e.Name)
            .IsRequired()
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("name");

        entity.Property(e => e.Phone)
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("phone");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.AddressId, "stores_address_id_foreign");

        entity.ToTable("stores").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.Address)
            .WithMany(p => p.Stores)
            .HasForeignKey(d => d.AddressId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("stores_address_id_foreign");
    }
}