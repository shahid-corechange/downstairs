using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class LaundryOrderHistoryConfiguration : IEntityTypeConfiguration<LaundryOrderHistory>
{
    public void Configure(EntityTypeBuilder<LaundryOrderHistory> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint unsigned")
            .HasColumnName("id");

        entity.Property(e => e.CauserId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("causer_id");

        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");

        entity.Property(e => e.DeletedAt)
            .HasColumnType("timestamp")
            .HasColumnName("deleted_at");

        entity.Property(e => e.LaundryOrderId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("laundry_order_id");

        entity.Property(e => e.Note)
            .IsRequired()
            .HasColumnType("text")
            .HasColumnName("note");

        entity.Property(e => e.Type)
            .IsRequired()
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("type");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.LaundryOrderId, "laundry_order_histories_laundry_order_id_foreign");

        entity.HasIndex(e => e.CauserId, "laundry_order_histories_causer_id_foreign");

        entity.ToTable("laundry_order_histories").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.Causer)
            .WithMany(p => p.LaundryOrderHistories)
            .HasForeignKey(d => d.CauserId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("laundry_order_histories_causer_id_foreign");

        entity.HasOne(d => d.LaundryOrder)
            .WithMany(p => p.LaundryOrderHistories)
            .HasForeignKey(d => d.LaundryOrderId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("laundry_order_histories_laundry_order_id_foreign");
    }
}