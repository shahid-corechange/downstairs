using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class ScheduleCleaningConfiguration : IEntityTypeConfiguration<ScheduleCleaning>
{
    public void Configure(EntityTypeBuilder<ScheduleCleaning> entity)
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

        entity.Property(e => e.LaundryType)
            .HasColumnType("varchar(255)")
            .HasColumnName("laundry_type");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.LaundryOrderId, "schedule_cleanings_laundry_order_id_foreign");

        entity.ToTable("schedule_cleanings").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.LaundryOrder)
            .WithMany(p => p.ScheduleCleanings)
            .HasForeignKey(d => d.LaundryOrderId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("schedule_cleanings_laundry_order_id_foreign");
    }
}