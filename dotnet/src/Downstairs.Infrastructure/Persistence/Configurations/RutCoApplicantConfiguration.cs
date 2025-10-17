using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class RutCoApplicantConfiguration : IEntityTypeConfiguration<RutCoApplicant>
{
    public void Configure(EntityTypeBuilder<RutCoApplicant> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("rut_co_applicants")
            .UseCollation("utf8mb4_unicode_ci");

        entity.HasIndex(e => e.UserId, "rut_co_applicants_user_id_foreign");

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");
        entity.Property(e => e.DeletedAt)
            .HasColumnType("timestamp")
            .HasColumnName("deleted_at");
        entity.Property(e => e.DialCode)
            .HasMaxLength(255)
            .HasColumnName("dial_code");
        entity.Property(e => e.IdentityNumber)
            .HasColumnType("text")
            .HasColumnName("identity_number");
        entity.Property(e => e.IsEnabled).HasColumnName("is_enabled");
        entity.Property(e => e.Name)
            .HasMaxLength(255)
            .HasColumnName("name");
        entity.Property(e => e.PauseEndDate).HasColumnName("pause_end_date");
        entity.Property(e => e.PauseStartDate).HasColumnName("pause_start_date");
        entity.Property(e => e.Phone)
            .HasMaxLength(255)
            .HasColumnName("phone");
        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");
        entity.Property(e => e.UserId).HasColumnName("user_id");

        entity.HasOne(d => d.User).WithMany(p => p.RutCoApplicants)
            .HasForeignKey(d => d.UserId)
            .HasConstraintName("rut_co_applicants_user_id_foreign");
    }
}

