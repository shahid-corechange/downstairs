using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class AuthenticationLogConfiguration : IEntityTypeConfiguration<AuthenticationLog>
{
    public void Configure(EntityTypeBuilder<AuthenticationLog> entity)
    {
        entity.Property(e => e.Id)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint unsigned")
            .HasColumnName("id");

        entity.Property(e => e.AuthenticatableId)
            .HasColumnType("bigint unsigned")
            .HasColumnName("authenticatable_id");

        entity.Property(e => e.AuthenticatableType)
            .IsRequired()
            .HasColumnType("varchar(255)")
            .HasColumnName("authenticatable_type");

        entity.Property(e => e.ClearedByUser)
            .HasColumnType("tinyint(1)")
            .HasDefaultValueSql("'0'")
            .HasColumnName("cleared_by_user");

        entity.Property(e => e.IpAddress)
            .HasMaxLength(45)
            .HasColumnType("varchar(45)")
            .HasColumnName("ip_address");

        entity.Property(e => e.Location)
            .HasColumnType("json")
            .HasColumnName("location");

        entity.Property(e => e.LoginAt)
            .HasColumnType("timestamp")
            .HasColumnName("login_at");

        entity.Property(e => e.LoginSuccessful)
            .HasColumnType("tinyint(1)")
            .HasDefaultValueSql("'0'")
            .HasColumnName("login_successful");

        entity.Property(e => e.LogoutAt)
            .HasColumnType("timestamp")
            .HasColumnName("logout_at");

        entity.Property(e => e.UserAgent)
            .HasColumnType("text")
            .HasColumnName("user_agent");

        entity.HasKey(e => e.Id)
            .HasName("PRIMARY");

        entity.HasIndex(e => new { e.AuthenticatableType, e.AuthenticatableId }, "authentication_log_authenticatable_type_authenticatable_id_index");

        entity.ToTable("authentication_log").UseCollation(DatabaseConstants.Collations.Unicode);
    }
}