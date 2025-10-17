using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class SubscriptionDetailConfiguration : IEntityTypeConfiguration<SubscriptionDetail>
{
    public void Configure(EntityTypeBuilder<SubscriptionDetail> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("subscription_details")
            .UseCollation(DatabaseConstants.Collations.Unicode);

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");
        entity.Property(e => e.DeletedAt)
            .HasColumnType("timestamp")
            .HasColumnName("deleted_at");
        entity.Property(e => e.PriceEstablish)
            .HasPrecision(8, 2)
            .HasColumnName("price_establish");
        entity.Property(e => e.PriceMaterial)
            .HasPrecision(8, 2)
            .HasColumnName("price_material");
        entity.Property(e => e.PricePerQuarters)
            .HasPrecision(8, 2)
            .HasColumnName("price_per_quarters");
        entity.Property(e => e.PricePerSquarefeet)
            .HasPrecision(8, 2)
            .HasColumnName("price_per_squarefeet");
        entity.Property(e => e.Squarefeet).HasColumnName("squarefeet");
        entity.Property(e => e.SubscriptionId).HasColumnName("subscription_id");
        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");
        entity.Property(e => e.VatId)
            .HasDefaultValueSql("'25'")
            .HasColumnName("vat_id");
    }
}