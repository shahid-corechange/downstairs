using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class StoreUserConfiguration : IEntityTypeConfiguration<StoreUser>
{
    public void Configure(EntityTypeBuilder<StoreUser> entity)
    {
        entity.Property(e => e.MyRowId)
            .ValueGeneratedOnAdd()
            .HasColumnType("bigint")
            .HasColumnName("my_row_id");

        entity.Property(e => e.StoreId)
            .HasColumnType("bigint")
            .HasColumnName("store_id");

        entity.Property(e => e.UserId)
            .HasColumnType("bigint")
            .HasColumnName("user_id");

        entity.HasKey(e => e.MyRowId)
            .HasName("PRIMARY");

        entity.HasIndex(e => e.StoreId, "store_users_store_id_foreign");

        entity.HasIndex(e => e.UserId, "store_users_user_id_foreign");

        entity.ToTable("store_users").UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasOne(d => d.Store)
            .WithMany(p => p.StoreUsers)
            .HasForeignKey(d => d.StoreId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("store_users_store_id_foreign");

        entity.HasOne(d => d.User)
            .WithMany(p => p.StoreUsers)
            .HasForeignKey(d => d.UserId)
            .OnDelete(DeleteBehavior.Cascade)
            .IsRequired()
            .HasConstraintName("store_users_user_id_foreign");
    }
}