using Microsoft.EntityFrameworkCore.Migrations;

#nullable disable

namespace Downstairs.Infrastructure.Persistence.Migrations
{
    /// <inheritdoc />
    public partial class FixDecimalUnsigned : Migration
    {
        /// <inheritdoc />
        protected override void Up(MigrationBuilder migrationBuilder)
        {
            // Fix decimal columns that should be unsigned
            migrationBuilder.Sql(@"
                ALTER TABLE `order_rows` MODIFY COLUMN `quantity` decimal(8,2) unsigned NOT NULL;
                ALTER TABLE `laundry_order_products` MODIFY COLUMN `discount` decimal(8,2) unsigned NOT NULL DEFAULT '0.00';
                ALTER TABLE `laundry_order_products` MODIFY COLUMN `price` decimal(8,2) unsigned NOT NULL;
                ALTER TABLE `subscription_products` MODIFY COLUMN `price` decimal(8,2) unsigned NOT NULL;
                ALTER TABLE `products` MODIFY COLUMN `price` decimal(8,2) unsigned NOT NULL;
                ALTER TABLE `price_adjustments` MODIFY COLUMN `price` decimal(8,2) unsigned NOT NULL;
                ALTER TABLE `price_adjustment_rows` MODIFY COLUMN `price` decimal(8,2) unsigned NOT NULL;
                ALTER TABLE `price_adjustment_rows` MODIFY COLUMN `previous_price` decimal(8,2) unsigned NOT NULL;
                ALTER TABLE `schedule_cleanings` MODIFY COLUMN `price` decimal(8,2) unsigned NOT NULL;
                ALTER TABLE `subscriptions` MODIFY COLUMN `price` decimal(8,2) unsigned NOT NULL;

                ALTER TABLE `addons` MODIFY COLUMN `price` decimal(8,2) unsigned NOT NULL;
                ALTER TABLE `fixed_price_rows` MODIFY COLUMN `price` decimal(8,2) unsigned NOT NULL;
                ALTER TABLE `price` MODIFY COLUMN `price` decimal(8,2) unsigned NOT NULL DEFAULT '0.00';
                ALTER TABLE `percentage` MODIFY COLUMN `price` decimal(8,2) unsigned NOT NULL DEFAULT '0.00';
                ALTER TABLE `order_fixed_price_rows` MODIFY COLUMN `price` decimal(8,2) unsigned NOT NULL;
                ALTER TABLE `schedule_cleaning_products` MODIFY COLUMN `price` decimal(8,2) unsigned NOT NULL;
                ALTER TABLE `schedule_cleaning_products` MODIFY COLUMN `quantity` decimal(8,2) unsigned NOT NULL;
                ALTER TABLE `schedule_items` MODIFY COLUMN `price` decimal(8,2) unsigned NOT NULL;
                ALTER TABLE `schedule_items` MODIFY COLUMN `quantity` decimal(8,2) unsigned NOT NULL DEFAULT '1.00';
                ALTER TABLE `services` MODIFY COLUMN `price` decimal(8,2) unsigned NOT NULL;
                ALTER TABLE `store_sale_products` MODIFY COLUMN `price` decimal(8,2) unsigned NOT NULL;
                ALTER TABLE `store_sale_products` MODIFY COLUMN `discount` decimal(8,2) unsigned NOT NULL DEFAULT '0.00';
                ALTER TABLE `unassign_subscriptions` MODIFY COLUMN `fixed_price` decimal(8,2) unsigned NOT NULL;
            ");
        }

        /// <inheritdoc />
        protected override void Down(MigrationBuilder migrationBuilder)
        {
            // Revert decimal columns back to signed
            migrationBuilder.Sql(@"
                ALTER TABLE `order_rows` MODIFY COLUMN `quantity` decimal(8,2) NOT NULL;
                ALTER TABLE `laundry_order_products` MODIFY COLUMN `discount` decimal(8,2) NOT NULL;
                ALTER TABLE `laundry_order_products` MODIFY COLUMN `price` decimal(8,2) NOT NULL;
                ALTER TABLE `subscription_products` MODIFY COLUMN `price` decimal(8,2) NOT NULL;
                ALTER TABLE `products` MODIFY COLUMN `price` decimal(8,2) NOT NULL;
                ALTER TABLE `price_adjustments` MODIFY COLUMN `price` decimal(8,2) NOT NULL;
                ALTER TABLE `price_adjustment_rows` MODIFY COLUMN `price` decimal(8,2) NOT NULL;
                ALTER TABLE `schedule_cleanings` MODIFY COLUMN `price` decimal(8,2) NOT NULL;
                ALTER TABLE `subscriptions` MODIFY COLUMN `price` decimal(8,2) NOT NULL;
            ");
        }
    }
}