using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class CustomerDiscountConfiguration : IEntityTypeConfiguration<CustomerDiscount>
{
    public void Configure(EntityTypeBuilder<CustomerDiscount> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint unsigned")
            .HasColumnName("id");

        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");

        entity.Property(e => e.DeletedAt)
            .HasColumnType("timestamp")
            .HasColumnName("deleted_at");

        entity.Property(e => e.EndDate)
            .HasColumnType("date")
            .HasColumnName("end_date");

        entity.Property(e => e.StartDate)
            .HasColumnType("date")
            .HasColumnName("start_date");

        entity.Property(e => e.Type)
            .IsRequired()
            .ValueGeneratedOnAdd()
            .HasColumnType("varchar(255)")
            .HasColumnName("type")
            .HasDefaultValueSql("'cleaning'");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.Property(e => e.UsageLimit)
            .HasColumnType("int unsigned")
            .HasColumnName("usage_limit");

        entity.Property(e => e.UserId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("user_id");

        entity.Property(e => e.Value)
            .HasColumnType("int")
            .HasDefaultValueSql("'0'")
            .HasColumnName("value");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.UserId, "customer_discounts_user_id_foreign");

        entity.HasIndex(e => e.Type, "customer_discounts_type_index");

        entity.HasIndex(e => e.StartDate, "customer_discounts_start_date_index");

        entity.HasIndex(e => e.EndDate, "customer_discounts_end_date_index");
        
        entity.HasIndex(e => e.CreatedAt, "customer_discounts_created_at_index");

        entity.ToTable("customer_discounts").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.User)
            .WithMany(p => p.CustomerDiscounts)
            .HasForeignKey(d => d.UserId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("customer_discounts_user_id_foreign");
    }
}