using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class UserOtpConfiguration : IEntityTypeConfiguration<UserOtp>
{
    public void Configure(EntityTypeBuilder<UserOtp> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("user_otps")
            .UseCollation("utf8mb4_unicode_ci");

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");
        entity.Property(e => e.ExpireAt)
            .HasColumnType("timestamp")
            .HasColumnName("expire_at");
        entity.Property(e => e.Info)
            .HasMaxLength(255)
            .HasColumnName("info");
        entity.Property(e => e.Otp)
            .HasMaxLength(255)
            .HasColumnName("otp");
        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");
        entity.Property(e => e.UserId).HasColumnName("user_id");
    }
}

