using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class NotificationConfiguration : IEntityTypeConfiguration<Notification>
{
    public void Configure(EntityTypeBuilder<Notification> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("notifications")
            .UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasIndex(e => e.UserId, "notifications_user_id_foreign");

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");
        entity.Property(e => e.Description)
            .HasColumnType("text")
            .HasColumnName("description");
        entity.Property(e => e.Hub)
            .HasMaxLength(255)
            .HasColumnName("hub");
        entity.Property(e => e.IsRead).HasColumnName("is_read");
        entity.Property(e => e.Title)
            .HasMaxLength(255)
            .HasColumnName("title");
        entity.Property(e => e.Type)
            .HasMaxLength(255)
            .HasColumnName("type");
        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");
        entity.Property(e => e.UserId).HasColumnName("user_id");

        entity.HasOne(d => d.User).WithMany(p => p.Notifications)
            .HasForeignKey(d => d.UserId)
            .HasConstraintName("notifications_user_id_foreign");
    }
}