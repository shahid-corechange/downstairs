using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class FeedbackConfiguration : IEntityTypeConfiguration<Feedback>
{
    public void Configure(EntityTypeBuilder<Feedback> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("feedbacks")
            .UseCollation("utf8mb4_unicode_ci");

        entity.HasIndex(e => new { e.FeedbackableType, e.FeedbackableId }, "feedbacks_feedbackable_type_feedbackable_id_index");

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");
        entity.Property(e => e.DeletedAt)
            .HasColumnType("timestamp")
            .HasColumnName("deleted_at");
        entity.Property(e => e.Description)
            .HasColumnType("text")
            .HasColumnName("description");
        entity.Property(e => e.FeedbackableId).HasColumnName("feedbackable_id");
        entity.Property(e => e.FeedbackableType).HasColumnName("feedbackable_type");
        entity.Property(e => e.Option)
            .HasMaxLength(255)
            .HasColumnName("option");
        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");
    }
}

