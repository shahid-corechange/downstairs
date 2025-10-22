using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Design;

namespace Downstairs.Infrastructure.Persistence;

/// <summary>
/// Design-time factory for DownstairsDbContext
/// This is used by EF Core tools (like migrations) when they need to create a DbContext instance
/// </summary>
public class DownstairsDbContextFactory : IDesignTimeDbContextFactory<DownstairsDbContext>
{
    public DownstairsDbContext CreateDbContext(string[] args)
    {
        var optionsBuilder = new DbContextOptionsBuilder<DownstairsDbContext>();

        // Use a dummy connection string for design-time operations (migrations)
        // No actual connection is needed for generating migrations
        var connectionString = "Server=localhost;Port=3306;Database=downstairs_design;Uid=root;Pwd=password;";

        // Use a fixed server version to avoid connection attempts during design-time
        optionsBuilder.UseMySql(connectionString, ServerVersion.Parse("8.0.0-mysql"));

        return new DownstairsDbContext(optionsBuilder.Options);
    }
}