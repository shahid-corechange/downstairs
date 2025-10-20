using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class TeamConfiguration : IEntityTypeConfiguration<Team>
{
    public void Configure(EntityTypeBuilder<Team> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint")
            .HasColumnName("id");

        entity.Property(e => e.Avatar)
            .HasColumnType("text")
            .HasColumnName("avatar");

        entity.Property(e => e.Color)
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
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
            .ValueGeneratedOnAdd()
            .HasColumnType("tinyint(1)")
            .HasColumnName("is_active")
            .HasDefaultValueSql("'1'");

        entity.Property(e => e.Name)
            .IsRequired()
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("name")
            .UseCollation(DatabaseConstants.Collations.Unicode);

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.ToTable("teams").UseCollation(DatabaseConstants.Collations.Unicode);
    }
}