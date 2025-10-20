using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class FeedbackConfiguration : IEntityTypeConfiguration<Feedback>
{
    public void Configure(EntityTypeBuilder<Feedback> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint")
            .HasColumnName("id");

        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");

        entity.Property(e => e.DeletedAt)
            .HasColumnType("timestamp")
            .HasColumnName("deleted_at");

        entity.Property(e => e.Description)
            .IsRequired()
            .HasColumnType("text")
            .HasColumnName("description");

        entity.Property(e => e.FeedbackableId)
            .HasColumnType("bigint")
            .HasColumnName("feedbackable_id");

        entity.Property(e => e.FeedbackableType)
            .IsRequired()
            .HasColumnType("varchar(255)")
            .HasColumnName("feedbackable_type");

        entity.Property(e => e.Option)
            .IsRequired()
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("option");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => new { e.FeedbackableType, e.FeedbackableId }, "feedbacks_feedbackable_type_feedbackable_id_index");

        entity.ToTable("feedbacks").UseCollation(DatabaseConstants.Collations.Unicode);
    }
}