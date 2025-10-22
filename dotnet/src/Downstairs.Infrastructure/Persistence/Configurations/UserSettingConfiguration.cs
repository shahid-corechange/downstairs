using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class UserSettingConfiguration : IEntityTypeConfiguration<UserSetting>
{
    public void Configure(EntityTypeBuilder<UserSetting> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint unsigned")
            .HasColumnName("id");

        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");

        entity.Property(e => e.Key)
            .IsRequired()
            .HasColumnType("varchar(255)")
            .HasColumnName("key");

        entity.Property(e => e.Type)
            .IsRequired()
            .HasColumnType("varchar(255)")
            .HasColumnName("type");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.Property(e => e.UserId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("user_id");

        entity.Property(e => e.Value)
            .IsRequired()
            .HasColumnType("varchar(255)")
            .HasColumnName("value");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.Key, "user_settings_key_index");

        entity.HasIndex(e => e.Type, "user_settings_type_index");

        entity.HasIndex(e => e.UserId, "user_settings_user_id_foreign");

        entity.HasIndex(e => e.Value, "user_settings_value_index");

        entity.ToTable("user_settings").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.User)
            .WithMany(p => p.UserSettings)
            .HasForeignKey(d => d.UserId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("user_settings_user_id_foreign");
    }
}