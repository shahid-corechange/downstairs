using Downstairs.Infrastructure.Persistence.Constants;
using Downstairs.Infrastructure.Persistence.Models;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Metadata.Builders;

namespace Downstairs.Infrastructure.Persistence.Configurations;

internal sealed class OrderConfiguration : IEntityTypeConfiguration<Order>
{
    public void Configure(EntityTypeBuilder<Order> entity)
    {
        entity.HasKey(e => e.Id).HasName("PRIMARY");

        entity
            .ToTable("orders")
            .UseCollation(DatabaseConstants.Collations.Unicode);

        entity.HasIndex(e => new { e.OrderableType, e.OrderableId }, "orderable_index");

        entity.HasIndex(e => e.CustomerId, "orders_customer_id_foreign");

        entity.HasIndex(e => e.InvoiceId, "orders_invoice_id_foreign");

        entity.HasIndex(e => e.OrderFixedPriceId, "orders_order_fixed_price_id_foreign");

        entity.HasIndex(e => e.ServiceId, "orders_service_id_foreign");

        entity.HasIndex(e => e.SubscriptionId, "orders_subscription_id_foreign");

        entity.HasIndex(e => e.UserId, "orders_user_id_foreign");

        entity.Property(e => e.Id).HasColumnName("id");
        entity.Property(e => e.CreatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("created_at");
        entity.Property(e => e.CustomerId).HasColumnName("customer_id");
        entity.Property(e => e.DeletedAt)
            .HasColumnType("timestamp")
            .HasColumnName("deleted_at");
        entity.Property(e => e.InvoiceId).HasColumnName("invoice_id");
        entity.Property(e => e.OrderFixedPriceId).HasColumnName("order_fixed_price_id");
        entity.Property(e => e.OrderableId).HasColumnName("orderable_id");
        entity.Property(e => e.OrderableType).HasColumnName("orderable_type");
        entity.Property(e => e.OrderedAt)
            .HasColumnType("datetime")
            .HasColumnName("ordered_at");
        entity.Property(e => e.PaidAt)
            .HasColumnType("timestamp")
            .HasColumnName("paid_at");
        entity.Property(e => e.PaidBy)
            .HasMaxLength(255)
            .HasDefaultValueSql("'invoice'")
            .HasColumnName("paid_by");
        entity.Property(e => e.ServiceId).HasColumnName("service_id");
        entity.Property(e => e.Status)
            .HasMaxLength(255)
            .HasDefaultValueSql("'draft'")
            .HasColumnName("status");
        entity.Property(e => e.SubscriptionId).HasColumnName("subscription_id");
        entity.Property(e => e.UpdatedAt)
            .HasColumnType("timestamp")
            .HasColumnName("updated_at");
        entity.Property(e => e.UserId).HasColumnName("user_id");

        entity.HasOne(d => d.Customer).WithMany(p => p.Orders)
            .HasForeignKey(d => d.CustomerId)
            .HasConstraintName("orders_customer_id_foreign");

        entity.HasOne(d => d.Invoice).WithMany(p => p.Orders)
            .HasForeignKey(d => d.InvoiceId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("orders_invoice_id_foreign");

        entity.HasOne(d => d.OrderFixedPrice).WithMany(p => p.Orders)
            .HasForeignKey(d => d.OrderFixedPriceId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("orders_order_fixed_price_id_foreign");

        entity.HasOne(d => d.Service).WithMany(p => p.Orders)
            .HasForeignKey(d => d.ServiceId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("orders_service_id_foreign");

        entity.HasOne(d => d.Subscription).WithMany(p => p.Orders)
            .HasForeignKey(d => d.SubscriptionId)
            .OnDelete(DeleteBehavior.Cascade)
            .HasConstraintName("orders_subscription_id_foreign");

        entity.HasOne(d => d.User).WithMany(p => p.Orders)
            .HasForeignKey(d => d.UserId)
            .HasConstraintName("orders_user_id_foreign");
    }
}