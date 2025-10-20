using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class OldOrderConfiguration : IEntityTypeConfiguration<OldOrder>
{
    public void Configure(EntityTypeBuilder<OldOrder> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint")
            .HasColumnName("id");

        entity.Property(e => e.OldOrderId)
            .HasColumnType("bigint")
            .HasColumnName("old_order_id");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.ToTable("old_orders").UseCollation(DatabaseConstants.Collations.Unicode);
    }
}