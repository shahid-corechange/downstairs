using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class TeamUserConfiguration : IEntityTypeConfiguration<TeamUser>
{
    public void Configure(EntityTypeBuilder<TeamUser> entity)
    {
        entity.Property(e => e.MyRowId)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint")
            .HasColumnName("my_row_id");

        entity.Property(e => e.TeamId)
            .HasColumnType("bigint")
            .HasColumnName("team_id");

        entity.Property(e => e.UserId)
            .HasColumnType("bigint")
            .HasColumnName("user_id");

        entity.HasKey(e => e.MyRowId)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.TeamId, "team_user_team_id_foreign");

        entity.HasIndex(e => e.UserId, "team_user_user_id_foreign");

        entity.ToTable("team_user").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.Team)
            .WithMany(p => p.TeamUsers)
            .HasForeignKey(d => d.TeamId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("team_user_team_id_foreign");

        entity.HasOne(d => d.User)
            .WithMany(p => p.TeamUsers)
            .HasForeignKey(d => d.UserId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("team_user_user_id_foreign");
    }
}