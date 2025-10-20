using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class RutCoApplicantConfiguration : IEntityTypeConfiguration<RutCoApplicant>
{
    public void Configure(EntityTypeBuilder<RutCoApplicant> entity)
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

        entity.Property(e => e.DialCode)
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("dial_code");

        entity.Property(e => e.IdentityNumber)
            .IsRequired()
            .HasColumnType("text")
            .HasColumnName("identity_number");

        entity.Property(e => e.IsEnabled)
            .HasColumnType("tinyint(1)")
            .HasColumnName("is_enabled");

        entity.Property(e => e.Name)
            .IsRequired()
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("name");

        entity.Property(e => e.PauseEndDate)
            .HasColumnType("date")
            .HasColumnName("pause_end_date");

        entity.Property(e => e.PauseStartDate)
            .HasColumnType("date")
            .HasColumnName("pause_start_date");

        entity.Property(e => e.Phone)
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("phone");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.Property(e => e.UserId)
            .HasColumnType("bigint")
            .HasColumnName("user_id");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.UserId, "rut_co_applicants_user_id_foreign");

        entity.ToTable("rut_co_applicants").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.User)
            .WithMany(p => p.RutCoApplicants)
            .HasForeignKey(d => d.UserId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("rut_co_applicants_user_id_foreign");
    }
}