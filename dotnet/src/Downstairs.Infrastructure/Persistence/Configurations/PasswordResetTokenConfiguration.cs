using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class PasswordResetTokenConfiguration : IEntityTypeConfiguration<PasswordResetToken>
{
    public void Configure(EntityTypeBuilder<PasswordResetToken> entity)
    {
        entity.Property(e => e.MyRowId)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint")
            .HasColumnName("my_row_id");

        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");

        entity.Property(e => e.Email)
            .IsRequired()
            .HasColumnType("varchar(255)")
            .HasColumnName("email");

        entity.Property(e => e.Token)
            .IsRequired()
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("token");

        entity.HasKey(e => e.MyRowId)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.Email, "password_reset_tokens_email_index");

        entity.ToTable("password_reset_tokens").UseCollation(DatabaseConstants.Collations.Unicode);
    }
}