using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class UserSettingConfiguration : IEntityTypeConfiguration<UserSetting>
{
    public void Configure(EntityTypeBuilder<UserSetting> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("user_settings")
            .UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasIndex(e => e.Key, "user_settings_key_index");

        entity.HasIndex(e => e.Type, "user_settings_type_index");

        entity.HasIndex(e => e.UserId, "user_settings_user_id_foreign");

        entity.HasIndex(e => e.Value, "user_settings_value_index");

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");
        entity.Property(e => e.Key).HasColumnName("key");
        entity.Property(e => e.Type).HasColumnName("type");
        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");
        entity.Property(e => e.UserId).HasColumnName("user_id");
        entity.Property(e => e.Value).HasColumnName("value");

        entity.HasOne(d => d.User).WithMany(p => p.UserSettings)
            .HasForeignKey(d => d.UserId)
            .HasConstraintName("user_settings_user_id_foreign");
    }
}