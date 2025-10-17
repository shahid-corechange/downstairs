using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class BlockDayConfiguration : IEntityTypeConfiguration<BlockDay>
{
    public void Configure(EntityTypeBuilder<BlockDay> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("block_days")
            .UseCollation(DatabaseConstants.Collations.Unicode);

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.BlockDate).HasColumnName("block_date");
        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");
        entity.Property(e => e.EndBlockTime)
            .HasColumnType("time")
            .HasColumnName("end_block_time");
        entity.Property(e => e.StartBlockTime)
            .HasColumnType("time")
            .HasColumnName("start_block_time");
        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");
    }
}