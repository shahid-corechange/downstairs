using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class PasswordResetTokenConfiguration : IEntityTypeConfiguration<PasswordResetToken>
{
    public void Configure(EntityTypeBuilder<PasswordResetToken> entity)
    {
        entity.HasKey(e => e.MyRowId).HasName("PRIMARY");

        entity
            .ToTable("password_reset_tokens")
            .UseCollation("utf8mb4_unicode_ci");

        entity.HasIndex(e => e.Email, "password_reset_tokens_email_index");

        entity.Property(e => e.MyRowId).HasColumnName("my_row_id");
        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");
        entity.Property(e => e.Email).HasColumnName("email");
        entity.Property(e => e.Token)
            .HasMaxLength(255)
            .HasColumnName("token");
    }
}

