using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class UserInfoConfiguration : IEntityTypeConfiguration<UserInfo>
{
    public void Configure(EntityTypeBuilder<UserInfo> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("user_infos")
            .UseCollation(DatabaseConstants.Collations.Unicode);

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.Avatar)
            .HasColumnType("text")
            .HasColumnName("avatar");
        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");
        entity.Property(e => e.Currency)
            .HasMaxLength(255)
            .HasColumnName("currency");
        entity.Property(e => e.DeletedAt)
            .HasColumnType("timestamp")
            .HasColumnName("deleted_at");
        entity.Property(e => e.Language)
            .HasMaxLength(255)
            .HasColumnName("language");
        entity.Property(e => e.Marketing).HasColumnName("marketing");
        entity.Property(e => e.NotificationMethod)
            .HasMaxLength(255)
            .HasDefaultValueSql("'app'")
            .HasColumnName("notification_method");
        entity.Property(e => e.Timezone)
            .HasMaxLength(255)
            .HasColumnName("timezone");
        entity.Property(e => e.TwoFactorAuth)
            .HasMaxLength(255)
            .HasDefaultValueSql("'disabled'")
            .HasColumnName("two_factor_auth");
        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");
        entity.Property(e => e.UserId).HasColumnName("user_id");
    }
}