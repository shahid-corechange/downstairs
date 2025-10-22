using Downstairs.Infrastructure.Persistence.Models;
using Downstairs.Infrastructure.Persistence.Seeders.Base;

namespace Downstairs.Infrastructure.Persistence.Seeders.Foundation;

/// <summary>
/// Seeds permissions data from Laravel PermissionsSeeder
/// Foundation seeder - must execute first due to role dependencies
/// </summary>
public class PermissionsSeeder : ValidatedSeeder
{
    public override int Order => 10;
    public override string Name => "Permissions";

    public PermissionsSeeder(IServiceProvider serviceProvider) : base(serviceProvider)
    {
    }

    public override async System.Threading.Tasks.Task SeedAsync(DownstairsDbContext context, IServiceProvider serviceProvider)
    {
        if (await ShouldSkipSeedingAsync(context.Permissions, Name))
        {
            return;
        }

        await ExecuteSeedingAsync(context, async () =>
        {
            var permissions = GetPermissions();
            await context.Permissions.AddRangeAsync(permissions);
        });

        // Validate seeded data
        await ValidateMinimumCountAsync(context.Permissions, 8, "Permissions");
    }

    private List<Permission> GetPermissions()
    {
        var now = DateTime.UtcNow;

        return new List<Permission>
        {
            new() { Name = "users.view", GuardName = "web", CreatedAt = now, UpdatedAt = now },
            new() { Name = "users.create", GuardName = "web", CreatedAt = now, UpdatedAt = now },
            new() { Name = "users.edit", GuardName = "web", CreatedAt = now, UpdatedAt = now },
            new() { Name = "users.delete", GuardName = "web", CreatedAt = now, UpdatedAt = now },
            new() { Name = "orders.view", GuardName = "web", CreatedAt = now, UpdatedAt = now },
            new() { Name = "orders.create", GuardName = "web", CreatedAt = now, UpdatedAt = now },
            new() { Name = "orders.edit", GuardName = "web", CreatedAt = now, UpdatedAt = now },
            new() { Name = "orders.delete", GuardName = "web", CreatedAt = now, UpdatedAt = now },
            new() { Name = "schedules.view", GuardName = "web", CreatedAt = now, UpdatedAt = now },
            new() { Name = "schedules.create", GuardName = "web", CreatedAt = now, UpdatedAt = now },
            new() { Name = "schedules.edit", GuardName = "web", CreatedAt = now, UpdatedAt = now },
            new() { Name = "schedules.delete", GuardName = "web", CreatedAt = now, UpdatedAt = now },
            new() { Name = "schedules.manage", GuardName = "web", CreatedAt = now, UpdatedAt = now },
            new() { Name = "reports.view", GuardName = "web", CreatedAt = now, UpdatedAt = now },
            new() { Name = "settings.view", GuardName = "web", CreatedAt = now, UpdatedAt = now },
            new() { Name = "settings.edit", GuardName = "web", CreatedAt = now, UpdatedAt = now },
            new() { Name = "admin.access", GuardName = "web", CreatedAt = now, UpdatedAt = now },
            new() { Name = "admin.manage", GuardName = "web", CreatedAt = now, UpdatedAt = now }
        };
    }
}