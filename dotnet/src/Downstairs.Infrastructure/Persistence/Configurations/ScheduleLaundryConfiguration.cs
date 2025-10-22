using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class ScheduleLaundryConfiguration : IEntityTypeConfiguration<ScheduleLaundry>
{
    public void Configure(EntityTypeBuilder<ScheduleLaundry> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint unsigned")
            .HasColumnName("id");

        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");

        entity.Property(e => e.LaundryOrderId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("laundry_order_id");

        entity.Property(e => e.Type)
            .IsRequired()
            .HasColumnType("varchar(255)")
            .HasColumnName("type");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.LaundryOrderId, "schedule_laundries_laundry_order_id_foreign");

        entity.HasIndex(e => e.Type, "schedule_laundries_type_index");

        entity.ToTable("schedule_laundries").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.LaundryOrder)
            .WithMany(p => p.ScheduleLaundries)
            .HasForeignKey(d => d.LaundryOrderId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("schedule_laundries_laundry_order_id_foreign");
    }
}