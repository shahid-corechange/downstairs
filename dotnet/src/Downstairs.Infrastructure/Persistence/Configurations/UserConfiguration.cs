using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class UserConfiguration : IEntityTypeConfiguration<User>
{
    public void Configure(EntityTypeBuilder<User> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("users")
            .UseCollation("utf8mb4_unicode_ci");

        entity.HasIndex(e => e.Cellphone, "users_cellphone_index");

        entity.HasIndex(e => e.Email, "users_email_unique").IsUnique();

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.Cellphone).HasColumnName("cellphone");
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
            .HasColumnName("dial_code");
        entity.Property(e => e.Email).HasColumnName("email");
        entity.Property(e => e.EmailVerifiedAt)
            .HasColumnType("timestamp")
            .HasColumnName("email_verified_at");
        entity.Property(e => e.FirstName)
            .HasMaxLength(255)
            .HasColumnName("first_name");
        entity.Property(e => e.IdentityNumber)
            .HasColumnType("text")
            .HasColumnName("identity_number");
        entity.Property(e => e.IdentityNumberVerifiedAt)
            .HasColumnType("timestamp")
            .HasColumnName("identity_number_verified_at");
        entity.Property(e => e.LastName)
            .HasMaxLength(255)
            .HasColumnName("last_name");
        entity.Property(e => e.LastSeen)
            .HasColumnType("timestamp")
            .HasColumnName("last_seen");
        entity.Property(e => e.Password)
            .HasMaxLength(255)
            .HasColumnName("password");
        entity.Property(e => e.RememberToken)
            .HasMaxLength(100)
            .HasColumnName("remember_token");
        entity.Property(e => e.Status)
            .HasMaxLength(255)
            .HasColumnName("status");
        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");
    }
}

