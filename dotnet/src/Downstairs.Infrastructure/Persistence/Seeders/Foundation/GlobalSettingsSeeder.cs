using Downstairs.Infrastructure.Persistence.Models;
using Downstairs.Infrastructure.Persistence.Seeders.Base;
using Microsoft.Extensions.Logging;

namespace Downstairs.Infrastructure.Persistence.Seeders.Foundation;

/// <summary>
/// Seeds global settings data from Laravel GlobalSettingsSeeder
/// Foundation seeder for application configuration
/// </summary>
public class GlobalSettingsSeeder : TransactionalSeeder
{
    public override int Order => 70;
    public override string Name => "GlobalSettings";

    public GlobalSettingsSeeder(IServiceProvider serviceProvider) : base(serviceProvider)
    {
    }

    public override async System.Threading.Tasks.Task SeedAsync(DownstairsDbContext context, IServiceProvider serviceProvider)
    {
        if (await ShouldSkipSeedingAsync(context.GlobalSettings, Name))
        {
            return;
        }

        await ExecuteWithTransactionAsync(context, async () =>
        {
            var settings = GetGlobalSettings();
            await context.GlobalSettings.AddRangeAsync(settings);
            await context.SaveChangesAsync();

            Logger.LogInformation("Seeded {Count} global settings", settings.Count);
        });
    }

    private List<GlobalSetting> GetGlobalSettings()
    {
        var now = DateTime.UtcNow;

        return new List<GlobalSetting>
        {
            // Application Settings
            new() { Key = "app.name", Value = "Downstairs", Type = "string", CreatedAt = now, UpdatedAt = now },
            new() { Key = "app.version", Value = "1.0.0", Type = "string", CreatedAt = now, UpdatedAt = now },
            new() { Key = "app.environment", Value = "production", Type = "string", CreatedAt = now, UpdatedAt = now },
            new() { Key = "app.debug", Value = "false", Type = "boolean", CreatedAt = now, UpdatedAt = now },
            new() { Key = "app.timezone", Value = "Europe/Stockholm", Type = "string", CreatedAt = now, UpdatedAt = now },
            new() { Key = "app.locale", Value = "sv", Type = "string", CreatedAt = now, UpdatedAt = now },
            new() { Key = "app.fallback_locale", Value = "en", Type = "string", CreatedAt = now, UpdatedAt = now },
            new() { Key = "app.url", Value = "https://downstairs.se", Type = "string", CreatedAt = now, UpdatedAt = now },
            
            // Business Settings
            new() { Key = "business.company_name", Value = "Downstairs AB", Type = "string", CreatedAt = now, UpdatedAt = now },
            new() { Key = "business.company_address", Value = "Stockholm, Sweden", Type = "string", CreatedAt = now, UpdatedAt = now },
            new() { Key = "business.contact_email", Value = "contact@downstairs.se", Type = "string", CreatedAt = now, UpdatedAt = now },
            new() { Key = "business.support_email", Value = "support@downstairs.se", Type = "string", CreatedAt = now, UpdatedAt = now },
            new() { Key = "business.phone", Value = "+46 8 123 456 78", Type = "string", CreatedAt = now, UpdatedAt = now },
            new() { Key = "business.vat_number", Value = "SE123456789001", Type = "string", CreatedAt = now, UpdatedAt = now },
            new() { Key = "business.currency", Value = "SEK", Type = "string", CreatedAt = now, UpdatedAt = now },
            new() { Key = "business.tax_rate", Value = "25.0", Type = "decimal", CreatedAt = now, UpdatedAt = now },
            
            // Order Settings
            new() { Key = "orders.default_status", Value = "pending", Type = "string", CreatedAt = now, UpdatedAt = now },
            new() { Key = "orders.auto_assign", Value = "true", Type = "boolean", CreatedAt = now, UpdatedAt = now },
            new() { Key = "orders.notification_enabled", Value = "true", Type = "boolean", CreatedAt = now, UpdatedAt = now },
            new() { Key = "orders.max_daily_orders", Value = "50", Type = "integer", CreatedAt = now, UpdatedAt = now },
            new() { Key = "orders.cancellation_window", Value = "24", Type = "integer", CreatedAt = now, UpdatedAt = now },
            
            // Payment Settings
            new() { Key = "payments.enabled", Value = "true", Type = "boolean", CreatedAt = now, UpdatedAt = now },
            new() { Key = "payments.methods", Value = "card,swish,invoice", Type = "string", CreatedAt = now, UpdatedAt = now },
            new() { Key = "payments.auto_capture", Value = "false", Type = "boolean", CreatedAt = now, UpdatedAt = now },
            new() { Key = "payments.invoice_due_days", Value = "30", Type = "integer", CreatedAt = now, UpdatedAt = now },
            
            // Notification Settings
            new() { Key = "notifications.email_enabled", Value = "true", Type = "boolean", CreatedAt = now, UpdatedAt = now },
            new() { Key = "notifications.sms_enabled", Value = "true", Type = "boolean", CreatedAt = now, UpdatedAt = now },
            new() { Key = "notifications.push_enabled", Value = "true", Type = "boolean", CreatedAt = now, UpdatedAt = now },
            new() { Key = "notifications.order_updates", Value = "true", Type = "boolean", CreatedAt = now, UpdatedAt = now },
            new() { Key = "notifications.marketing", Value = "false", Type = "boolean", CreatedAt = now, UpdatedAt = now },
            
            // Security Settings
            new() { Key = "security.session_timeout", Value = "120", Type = "integer", CreatedAt = now, UpdatedAt = now },
            new() { Key = "security.max_login_attempts", Value = "5", Type = "integer", CreatedAt = now, UpdatedAt = now },
            new() { Key = "security.password_min_length", Value = "8", Type = "integer", CreatedAt = now, UpdatedAt = now },
            new() { Key = "security.require_2fa", Value = "false", Type = "boolean", CreatedAt = now, UpdatedAt = now },
            new() { Key = "security.api_rate_limit", Value = "1000", Type = "integer", CreatedAt = now, UpdatedAt = now },
            
            // Integration Settings
            new() { Key = "integrations.fortnox_enabled", Value = "true", Type = "boolean", CreatedAt = now, UpdatedAt = now },
            new() { Key = "integrations.kivra_enabled", Value = "true", Type = "boolean", CreatedAt = now, UpdatedAt = now },
            new() { Key = "integrations.sync_interval", Value = "60", Type = "integer", CreatedAt = now, UpdatedAt = now },
            
            // Feature Flags
            new() { Key = "features.advanced_scheduling", Value = "true", Type = "boolean", CreatedAt = now, UpdatedAt = now },
            new() { Key = "features.real_time_tracking", Value = "true", Type = "boolean", CreatedAt = now, UpdatedAt = now },
            new() { Key = "features.customer_portal", Value = "true", Type = "boolean", CreatedAt = now, UpdatedAt = now },
            new() { Key = "features.mobile_app", Value = "true", Type = "boolean", CreatedAt = now, UpdatedAt = now },
            new() { Key = "features.analytics_dashboard", Value = "true", Type = "boolean", CreatedAt = now, UpdatedAt = now }
        };
    }
}