using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class ServiceConfiguration : IEntityTypeConfiguration<Service>
{
    public void Configure(EntityTypeBuilder<Service> entity)
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

        entity.Property(e => e.FortnoxArticleId)
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("fortnox_article_id");

        entity.Property(e => e.HasRut)
            .HasColumnType("tinyint(1)")
            .HasColumnName("has_rut");

        entity.Property(e => e.MembershipType)
            .IsRequired()
            .HasColumnType("varchar(255)")
            .HasColumnName("membership_type");

        entity.Property(e => e.Price)
            .HasColumnType("decimal(8,2)")
            .HasColumnName("price");

        entity.Property(e => e.ThumbnailImage)
            .HasColumnType("text")
            .HasColumnName("thumbnail_image");

        entity.Property(e => e.Type)
            .IsRequired()
            .HasColumnType("varchar(255)")
            .HasColumnName("type");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.Property(e => e.VatGroup)
            .ValueGeneratedOnAdd()
            .HasColumnType("tinyint")
            .HasColumnName("vat_group")
            .HasDefaultValueSql("'25'");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.MembershipType, "services_membership_type_index");

        entity.HasIndex(e => e.Type, "services_type_index");

        entity.ToTable("services").UseCollation(DatabaseConstants.Collations.Unicode);
    }
}