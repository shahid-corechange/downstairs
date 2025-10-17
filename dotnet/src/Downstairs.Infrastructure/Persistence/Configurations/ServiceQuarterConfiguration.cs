using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class ServiceQuarterConfiguration : IEntityTypeConfiguration<ServiceQuarter>
{
    public void Configure(EntityTypeBuilder<ServiceQuarter> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("service_quarters")
            .UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasIndex(e => e.ServiceId, "service_quarters_service_id_foreign");

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");
        entity.Property(e => e.MaxSquareMeters).HasColumnName("max_square_meters");
        entity.Property(e => e.MinSquareMeters).HasColumnName("min_square_meters");
        entity.Property(e => e.Quarters).HasColumnName("quarters");
        entity.Property(e => e.ServiceId).HasColumnName("service_id");
        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.HasOne(d => d.Service).WithMany(p => p.ServiceQuarters)
            .HasForeignKey(d => d.ServiceId)
            .HasConstraintName("service_quarters_service_id_foreign");
    }
}