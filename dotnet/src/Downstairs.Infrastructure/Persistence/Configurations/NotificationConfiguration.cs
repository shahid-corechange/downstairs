using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class NotificationConfiguration : IEntityTypeConfiguration<Notification>
{
    public void Configure(EntityTypeBuilder<Notification> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint")
            .HasColumnName("id");

        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");

        entity.Property(e => e.Description)
            .IsRequired()
            .HasColumnType("text")
            .HasColumnName("description");

        entity.Property(e => e.Hub)
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("hub");

        entity.Property(e => e.IsRead)
            .HasColumnType("tinyint(1)")
            .HasColumnName("is_read");

        entity.Property(e => e.Title)
            .IsRequired()
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("title");

        entity.Property(e => e.Type)
            .IsRequired()
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("type");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.Property(e => e.UserId)
            .HasColumnType("bigint")
            .HasColumnName("user_id");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.UserId, "notifications_user_id_foreign");

        entity.ToTable("notifications").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.User)
            .WithMany(p => p.Notifications)
            .HasForeignKey(d => d.UserId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("notifications_user_id_foreign");
    }
}