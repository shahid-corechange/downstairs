using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class OauthRemoteTokenConfiguration : IEntityTypeConfiguration<OauthRemoteToken>
{
    public void Configure(EntityTypeBuilder<OauthRemoteToken> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("oauth_remote_tokens")
            .UseCollation("utf8mb4_unicode_ci");

        entity.HasIndex(e => e.AppName, "oauth_remote_tokens_app_name_unique").IsUnique();

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.AccessExpiresAt)
            .HasColumnType("timestamp")
            .HasColumnName("access_expires_at");
        entity.Property(e => e.AccessToken)
            .HasColumnType("text")
            .HasColumnName("access_token");
        entity.Property(e => e.AppName).HasColumnName("app_name");
        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");
        entity.Property(e => e.RefreshExpiresAt)
            .HasColumnType("timestamp")
            .HasColumnName("refresh_expires_at");
        entity.Property(e => e.RefreshToken)
            .HasColumnType("text")
            .HasColumnName("refresh_token");
        entity.Property(e => e.Scope)
            .HasColumnType("text")
            .HasColumnName("scope");
        entity.Property(e => e.TokenType)
            .HasMaxLength(255)
            .HasColumnName("token_type");
        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");
    }
}

