using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class PersonalAccessTokenConfiguration : IEntityTypeConfiguration<PersonalAccessToken>
{
    public void Configure(EntityTypeBuilder<PersonalAccessToken> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("personal_access_tokens")
            .UseCollation("utf8mb4_unicode_ci");

        entity.HasIndex(e => e.Name, "personal_access_tokens_name_unique").IsUnique();

        entity.HasIndex(e => e.Token, "personal_access_tokens_token_unique").IsUnique();

        entity.HasIndex(e => new { e.TokenableType, e.TokenableId }, "personal_access_tokens_tokenable_type_tokenable_id_index");

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.Abilities)
            .HasColumnType("text")
            .HasColumnName("abilities");
        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");
        entity.Property(e => e.ExpiresAt)
            .HasColumnType("timestamp")
            .HasColumnName("expires_at");
        entity.Property(e => e.LastUsedAt)
            .HasColumnType("timestamp")
            .HasColumnName("last_used_at");
        entity.Property(e => e.Name).HasColumnName("name");
        entity.Property(e => e.Token)
            .HasMaxLength(64)
            .HasColumnName("token");
        entity.Property(e => e.TokenableId).HasColumnName("tokenable_id");
        entity.Property(e => e.TokenableType).HasColumnName("tokenable_type");
        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");
    }
}

