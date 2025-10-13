using Downstairs.Application.Common.Interfaces;
using Downstairs.Infrastructure.Dapr;
using Downstairs.Infrastructure.Persistence;
using Downstairs.Infrastructure.Persistence.Repositories;
using Downstairs.Infrastructure.Resilience;
using Microsoft.EntityFrameworkCore;
using Microsoft.Extensions.Configuration;
using Microsoft.Extensions.DependencyInjection;
using Microsoft.Extensions.Logging;

namespace Downstairs.Infrastructure;

/// <summary>
/// Infrastructure layer dependency injection configuration
/// </summary>
public static class DependencyInjection
{
    /// <summary>
    /// Add infrastructure services to the container
    /// </summary>
    public static IServiceCollection AddInfrastructure(
        this IServiceCollection services, 
        IConfiguration configuration)
    {
        // Add database context
        services.AddDbContext<DownstairsDbContext>(options =>
        {
            var connectionString = configuration.GetConnectionString("DefaultConnection");
            options.UseMySql(connectionString, ServerVersion.AutoDetect(connectionString), 
                mysqlOptions =>
                {
                    mysqlOptions.MigrationsAssembly(typeof(DownstairsDbContext).Assembly.FullName);
                    mysqlOptions.EnableRetryOnFailure(
                        maxRetryCount: 3,
                        maxRetryDelay: TimeSpan.FromSeconds(30),
                        errorNumbersToAdd: null);
                });

            // Enable sensitive data logging in development
            if (configuration.GetValue<bool>("EnableSensitiveDataLogging"))
            {
                options.EnableSensitiveDataLogging();
            }
        });

        // Add repositories
        services.AddScoped<ICustomerRepository, CustomerRepository>();
        services.AddScoped<IInvoiceRepository, InvoiceRepository>();
        services.AddScoped<IUnitOfWork, UnitOfWork>();

        // Add Dapr client (will be registered by the host application)

        // Add Dapr services
        services.AddScoped<IEventPublisher, DaprEventPublisher>();
        services.AddScoped<IServiceInvoker, DaprServiceInvoker>();

        // Add resilience strategies
        services.AddResilienceStrategies(configuration);

        // Add HTTP clients
        services.AddHttpClient("FortnoxClient", client =>
        {
            var baseUrl = configuration.GetValue<string>("Integrations:Fortnox:BaseUrl");
            if (!string.IsNullOrEmpty(baseUrl))
            {
                client.BaseAddress = new Uri(baseUrl);
            }
            
            var accessToken = configuration.GetValue<string>("Integrations:Fortnox:AccessToken");
            if (!string.IsNullOrEmpty(accessToken))
            {
                client.DefaultRequestHeaders.Add("Access-Token", accessToken);
            }
        });

        services.AddHttpClient("KivraClient", client =>
        {
            var baseUrl = configuration.GetValue<string>("Integrations:Kivra:BaseUrl");
            if (!string.IsNullOrEmpty(baseUrl))
            {
                client.BaseAddress = new Uri(baseUrl);
            }
        });

        return services;
    }

    /// <summary>
    /// Apply database migrations
    /// </summary>
    public static async Task ApplyMigrationsAsync(IServiceProvider serviceProvider)
    {
        using var scope = serviceProvider.CreateScope();
        var context = scope.ServiceProvider.GetRequiredService<DownstairsDbContext>();
        var logger = scope.ServiceProvider.GetRequiredService<ILogger<DownstairsDbContext>>();

        try
        {
            logger.LogInformation("Applying database migrations...");
            await context.Database.MigrateAsync();
            logger.LogInformation("Database migrations applied successfully");
        }
        catch (Exception ex)
        {
            logger.LogError(ex, "An error occurred while applying database migrations");
            throw;
        }
    }

    /// <summary>
    /// Seed initial data
    /// </summary>
    public static async Task SeedDataAsync(IServiceProvider serviceProvider)
    {
        using var scope = serviceProvider.CreateScope();
        var context = scope.ServiceProvider.GetRequiredService<DownstairsDbContext>();
        var logger = scope.ServiceProvider.GetRequiredService<ILogger<DownstairsDbContext>>();

        try
        {
            if (!context.Customers.Any())
            {
                logger.LogInformation("Seeding initial data...");
                
                // Add sample data here if needed
                // var sampleCustomer = Customer.Create(...);
                // context.Customers.Add(sampleCustomer);
                
                await context.SaveChangesAsync();
                logger.LogInformation("Initial data seeded successfully");
            }
        }
        catch (Exception ex)
        {
            logger.LogError(ex, "An error occurred while seeding initial data");
            throw;
        }
    }
}