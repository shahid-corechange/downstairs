using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class ServiceAddonConfiguration : IEntityTypeConfiguration<ServiceAddon>
{
    public void Configure(EntityTypeBuilder<ServiceAddon> entity)
    {
        entity.Property(e => e.MyRowId)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint unsigned")
            .HasColumnName("my_row_id");

        entity.Property(e => e.AddonId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("addon_id");

        entity.Property(e => e.ServiceId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("service_id");

        entity.HasKey(e => e.MyRowId)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.AddonId, "service_addons_addon_id_foreign");

        entity.HasIndex(e => e.ServiceId, "service_addons_service_id_foreign");

        entity.ToTable("service_addons").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.Addon)
            .WithMany(p => p.ServiceAddons)
            .HasForeignKey(d => d.AddonId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("service_addons_addon_id_foreign");

        entity.HasOne(d => d.Service)
            .WithMany(p => p.ServiceAddons)
            .HasForeignKey(d => d.ServiceId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("service_addons_service_id_foreign");
    }
}