using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class TeamUserConfiguration : IEntityTypeConfiguration<TeamUser>
{
    public void Configure(EntityTypeBuilder<TeamUser> entity)
    {
        entity.HasKey(e => e.MyRowId).HasName("PRIMARY");

        entity
            .ToTable("team_user")
            .UseCollation("utf8mb4_unicode_ci");

        entity.HasIndex(e => e.TeamId, "team_user_team_id_foreign");

        entity.HasIndex(e => e.UserId, "team_user_user_id_foreign");

        entity.Property(e => e.MyRowId).HasColumnName("my_row_id");
        entity.Property(e => e.TeamId).HasColumnName("team_id");
        entity.Property(e => e.UserId).HasColumnName("user_id");

        entity.HasOne(d => d.Team).WithMany(p => p.TeamUsers)
            .HasForeignKey(d => d.TeamId)
            .HasConstraintName("team_user_team_id_foreign");

        entity.HasOne(d => d.User).WithMany(p => p.TeamUsers)
            .HasForeignKey(d => d.UserId)
            .HasConstraintName("team_user_user_id_foreign");
    }
}

