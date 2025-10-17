using Downstairs.Application.Common.Interfaces;
using Downstairs.Infrastructure.Caching;
using Downstairs.Infrastructure.Dapr;
using Downstairs.Infrastructure.Locking;
using Downstairs.Infrastructure.Persistence;
using Downstairs.Infrastructure.Persistence.Repositories;
using Downstairs.Infrastructure.Resilience;
using Downstairs.ServiceDefaults.Configuration;
using Microsoft.EntityFrameworkCore;
using Microsoft.Extensions.Configuration;
using Microsoft.Extensions.DependencyInjection;
using Microsoft.Extensions.Logging;
using StackExchange.Redis;

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
            var connectionString = ConnectionStringHelper.GetRequiredConnectionString(configuration, "downstairsdb");

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

        // Add repositories and unit of work for the scaffolded persistence layer
        services.AddScoped<ICustomerRepository, CustomerRepository>();
        services.AddScoped<IInvoiceRepository, InvoiceRepository>();
        services.AddScoped<IUnitOfWork, UnitOfWork>();

        // Add distributed caching and locking (Redis)
        var redisConnectionString = configuration.GetConnectionString("redis");
        if (!string.IsNullOrEmpty(redisConnectionString))
        {
            services.AddStackExchangeRedisCache(options =>
            {
                options.Configuration = redisConnectionString;
                options.InstanceName = "Downstairs";
            });

            // Add Redis connection for distributed locking
            services.AddSingleton<IConnectionMultiplexer>(sp =>
            {
                return ConnectionMultiplexer.Connect(redisConnectionString);
            });

            services.AddScoped<ICacheService, RedisCacheService>();
            services.AddScoped<IDistributedLockService, RedisDistributedLockService>();
        }
        else
        {
            // Fallback to in-memory cache if Redis is not available
            services.AddMemoryCache();
            services.AddScoped<ICacheService, MemoryCacheService>();
            // No distributed locking service available without Redis
        }

        // Add Dapr client (will be registered by the host application)

        // Add Dapr services
        services.AddScoped<IEventPublisher, DaprEventPublisher>();
        services.AddScoped<IServiceInvoker, DaprServiceInvoker>();

        // Add resilience strategies
        services.AddResilienceStrategies(configuration);

        // Add HTTP clients with SSL configuration for development
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
        })
        .ConfigurePrimaryHttpMessageHandler(() => CreateHttpClientHandler(configuration));

        services.AddHttpClient("KivraClient", client =>
        {
            var baseUrl = configuration.GetValue<string>("Integrations:Kivra:BaseUrl");
            if (!string.IsNullOrEmpty(baseUrl))
            {
                client.BaseAddress = new Uri(baseUrl);
            }
        })
        .ConfigurePrimaryHttpMessageHandler(() => CreateHttpClientHandler(configuration));

        return services;
    }

    /// <summary>
    /// Create HttpClientHandler with appropriate SSL configuration for development
    /// </summary>
    private static HttpClientHandler CreateHttpClientHandler(IConfiguration configuration)
    {
        var handler = new HttpClientHandler();

        // In development, allow self-signed certificates
        var isDevelopment = configuration.GetValue<string>("ASPNETCORE_ENVIRONMENT") == "Development" ||
                           configuration.GetValue<string>("DOTNET_ENVIRONMENT") == "Development";

        if (isDevelopment)
        {
            handler.ServerCertificateCustomValidationCallback = (sender, cert, chain, sslPolicyErrors) =>
            {
                // Accept all certificates in development
                return true;
            };
        }

        return handler;
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
    /// Seed initial data using the dedicated DatabaseSeeder
    /// </summary>
    public static async Task SeedDataAsync(IServiceProvider serviceProvider)
    {
        await DatabaseSeeder.SeedAsync(serviceProvider);
    }
}