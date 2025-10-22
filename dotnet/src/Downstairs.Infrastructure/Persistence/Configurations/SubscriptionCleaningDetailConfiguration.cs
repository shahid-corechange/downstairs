using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class SubscriptionCleaningDetailConfiguration : IEntityTypeConfiguration<SubscriptionCleaningDetail>
{
    public void Configure(EntityTypeBuilder<SubscriptionCleaningDetail> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint unsigned")
            .HasColumnName("id");

        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");

        entity.Property(e => e.EndTime)
            .HasColumnType("time")
            .HasColumnName("end_time");

        entity.Property(e => e.PropertyId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("property_id");

        entity.Property(e => e.Quarters)
            .HasColumnType("smallint unsigned")
            .HasColumnName("quarters");

        entity.Property(e => e.StartTime)
            .HasColumnType("time")
            .HasColumnName("start_time");

        entity.Property(e => e.TeamId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("team_id");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.PropertyId, "subscription_cleaning_details_property_id_foreign");

        entity.HasIndex(e => e.TeamId, "subscription_cleaning_details_team_id_foreign");

        entity.ToTable("subscription_cleaning_details").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.Property)
            .WithMany(p => p.SubscriptionCleaningDetails)
            .HasForeignKey(d => d.PropertyId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("subscription_cleaning_details_property_id_foreign");

        entity.HasOne(d => d.Team)
            .WithMany(p => p.SubscriptionCleaningDetails)
            .HasForeignKey(d => d.TeamId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("subscription_cleaning_details_team_id_foreign");
    }
}