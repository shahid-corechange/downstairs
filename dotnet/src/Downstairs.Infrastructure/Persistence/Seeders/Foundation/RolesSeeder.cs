using Downstairs.Infrastructure.Persistence.Models;
using Downstairs.Infrastructure.Persistence.Seeders.Base;
using Microsoft.EntityFrameworkCore;
using Microsoft.Extensions.Logging;

namespace Downstairs.Infrastructure.Persistence.Seeders.Foundation;

/// <summary>
/// Seeds roles data from Laravel RolesSeeder
/// Foundation seeder - executes after permissions due to many-to-many relationship
/// </summary>
public class RolesSeeder : TransactionalSeeder
{
    public override int Order => 20;
    public override string Name => "Roles";

    public RolesSeeder(IServiceProvider serviceProvider) : base(serviceProvider)
    {
    }

    public override async System.Threading.Tasks.Task SeedAsync(DownstairsDbContext context, IServiceProvider serviceProvider)
    {
        if (await ShouldSkipSeedingAsync(context.Roles, Name))
        {
            return;
        }

        await ExecuteWithTransactionAsync(context, async () =>
        {
            var roles = GetRoles();
            await context.Roles.AddRangeAsync(roles);
            await context.SaveChangesAsync();

            // Assign permissions to roles after both are created
            await AssignRolePermissionsAsync(context);
        });
    }

    private List<Role> GetRoles()
    {
        var now = DateTime.UtcNow;

        return new List<Role>
        {
            new() { Name = "Superadmin", GuardName = "web", CreatedAt = now, UpdatedAt = now },
            new() { Name = "Employee", GuardName = "web", CreatedAt = now, UpdatedAt = now },
            new() { Name = "Customer", GuardName = "web", CreatedAt = now, UpdatedAt = now },
            new() { Name = "Company", GuardName = "web", CreatedAt = now, UpdatedAt = now },
            new() { Name = "Worker", GuardName = "web", CreatedAt = now, UpdatedAt = now }
        };
    }

    private async System.Threading.Tasks.Task AssignRolePermissionsAsync(DownstairsDbContext context)
    {
        var superadmin = await context.Roles.FirstAsync(r => r.Name == "Superadmin");
        var employee = await context.Roles.FirstAsync(r => r.Name == "Employee");
        var worker = await context.Roles.FirstAsync(r => r.Name == "Worker");

        var allPermissions = await context.Permissions.ToListAsync();

        // Superadmin gets all permissions
        superadmin.Permissions = allPermissions;

        // Employee gets user and order permissions
        var employeePermissions = allPermissions.Where(p =>
            p.Name.StartsWith("users.") ||
            p.Name.StartsWith("orders.") ||
            p.Name.StartsWith("reports.")).ToList();
        employee.Permissions = employeePermissions;

        // Worker gets schedule permissions
        var workerPermissions = allPermissions.Where(p =>
            p.Name.StartsWith("schedules.") &&
            !p.Name.Contains("manage")).ToList();
        worker.Permissions = workerPermissions;

        Logger.LogInformation("Assigned permissions to roles: Superadmin={SuperadminCount}, Employee={EmployeeCount}, Worker={WorkerCount}",
            superadmin.Permissions.Count, employee.Permissions.Count, worker.Permissions.Count);
    }
}