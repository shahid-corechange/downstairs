using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class UserConfiguration : IEntityTypeConfiguration<User>
{
    public void Configure(EntityTypeBuilder<User> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint unsigned")
            .HasColumnName("id");

        entity.Property(e => e.Cellphone)
            .HasColumnType("varchar(255)")
            .HasColumnName("cellphone");

        entity.Property(e => e.CellphoneVerifiedAt)
            .HasColumnType("timestamp")
            .HasColumnName("cellphone_verified_at");

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

        entity.Property(e => e.Email)
            .HasColumnType("varchar(255)")
            .HasColumnName("email");

        entity.Property(e => e.EmailVerifiedAt)
            .HasColumnType("timestamp")
            .HasColumnName("email_verified_at");

        entity.Property(e => e.FirstName)
            .IsRequired()
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("first_name");

        entity.Property(e => e.IdentityNumber)
            .HasColumnType("text")
            .HasColumnName("identity_number");

        entity.Property(e => e.IsCompanyContact)
            .HasColumnType("tinyint(1)")
            .HasColumnName("is_company_contact")
            .HasDefaultValueSql("'0'");

        entity.Property(e => e.IdentityNumberVerifiedAt)
            .HasColumnType("timestamp")
            .HasColumnName("identity_number_verified_at");

        entity.Property(e => e.LastName)
            .IsRequired()
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("last_name");

        entity.Property(e => e.LastSeen)
            .HasColumnType("timestamp")
            .HasColumnName("last_seen");

        entity.Property(e => e.Password)
            .IsRequired()
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("password");

        entity.Property(e => e.RememberToken)
            .HasMaxLength(100)
            .HasColumnType("varchar(100)")
            .HasColumnName("remember_token");

        entity.Property(e => e.Status)
            .IsRequired()
            .HasMaxLength(255)
            .HasColumnType("varchar(255)")
            .HasColumnName("status");

        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.Cellphone, "users_cellphone_index");

        entity.HasIndex(e => e.Email, "users_email_unique")
            .IsUnique();

        entity.ToTable("users").UseCollation(DatabaseConstants.Collations.Unicode);
    }
}