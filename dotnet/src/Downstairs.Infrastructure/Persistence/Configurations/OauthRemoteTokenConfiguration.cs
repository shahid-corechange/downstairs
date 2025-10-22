using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class OauthRemoteTokenConfiguration : IEntityTypeConfiguration<OauthRemoteToken>
{
    public void Configure(EntityTypeBuilder<OauthRemoteToken> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint unsigned")
            .HasColumnName("id");

        entity.Property(e => e.AccessExpiresAt)
            .HasColumnType("timestamp")
            .HasColumnName("access_expires_at");

        entity.Property(e => e.AccessToken)
            .IsRequired()
            .HasColumnType("text")
            .HasColumnName("access_token");

        entity.Property(e => e.AppName)
            .IsRequired()
            .HasColumnType("varchar(255)")
            .HasColumnName("app_name");

        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");

        entity.Property(e => e.RefreshExpiresAt)
            .HasColumnType("timestamp")
            .HasColumnName("refresh_expires_at");

        entity.Property(e => e.RefreshToken)
            .IsRequired()
            .HasColumnType("text")
            .HasColumnName("refresh_token");

        entity.Property(e => e.Scope)
            .IsRequired()
            .HasColumnType("text")
            .HasColumnName("scope");

        entity.Property(e => e.TokenType)
            .IsRequired()
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("token_type");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.AppName, "oauth_remote_tokens_app_name_unique")
            .IsUnique();

        entity.ToTable("oauth_remote_tokens").UseCollation(DatabaseConstants.Collations.Unicode);
    }
}