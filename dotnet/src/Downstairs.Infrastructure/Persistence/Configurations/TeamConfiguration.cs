using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class TeamConfiguration : IEntityTypeConfiguration<Team>
{
    public void Configure(EntityTypeBuilder<Team> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("teams")
            .UseCollation(DatabaseConstants.Collations.Unicode);

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.Avatar)
            .HasColumnType("text")
            .HasColumnName("avatar");
        entity.Property(e => e.Color)
            .HasMaxLength(255)
            .HasColumnName("color");
        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");
        entity.Property(e => e.DeletedAt)
            .HasColumnType("timestamp")
            .HasColumnName("deleted_at");
        entity.Property(e => e.Description)
            .HasColumnType("text")
            .HasColumnName("description");
        entity.Property(e => e.IsActive)
            .IsRequired()
            .HasDefaultValueSql("'1'")
            .HasColumnName("is_active");
        entity.Property(e => e.Name)
            .HasMaxLength(255)
            .HasColumnName("name")
            .UseCollation(DatabaseConstants.Collations.Unicode);
        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");
    }
}