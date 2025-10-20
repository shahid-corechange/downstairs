using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class LeaveRegistrationDetailConfiguration : IEntityTypeConfiguration<LeaveRegistrationDetail>
{
    public void Configure(EntityTypeBuilder<LeaveRegistrationDetail> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint")
            .HasColumnName("id");

        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");

        entity.Property(e => e.EndAt)
            .HasColumnType("datetime")
            .HasColumnName("end_at");

        entity.Property(e => e.FortnoxAbsenceTransactionId)
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("fortnox_absence_transaction_id");

        entity.Property(e => e.LeaveRegistrationId)
            .HasColumnType("bigint")
            .HasColumnName("leave_registration_id");

        entity.Property(e => e.StartAt)
            .HasColumnType("datetime")
            .HasColumnName("start_at");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.LeaveRegistrationId, "leave_registration_details_leave_registration_id_foreign");

        entity.ToTable("leave_registration_details").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.LeaveRegistration)
            .WithMany(p => p.LeaveRegistrationDetails)
            .HasForeignKey(d => d.LeaveRegistrationId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("leave_registration_details_leave_registration_id_foreign");
    }
}