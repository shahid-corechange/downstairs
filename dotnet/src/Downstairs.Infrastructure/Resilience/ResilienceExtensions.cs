using Microsoft.Extensions.Configuration;
using Microsoft.Extensions.DependencyInjection;
using Microsoft.Extensions.Logging;
using Polly;
using Polly.Extensions.Http;
using Polly.Timeout;

namespace Downstairs.Infrastructure.Resilience;

/// <summary>
/// Extension methods for configuring resilience policies
/// </summary>
public static class ResilienceExtensions
{
    /// <summary>
    /// Configure resilience strategies for the application
    /// </summary>
    public static IServiceCollection AddResilienceStrategies(this IServiceCollection services, IConfiguration configuration)
    {
        // For now, we'll use simple retry policies with HTTP clients
        // This is a placeholder for proper Polly v8 configuration
        
        return services;
    }


}