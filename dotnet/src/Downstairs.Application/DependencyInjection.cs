using System.Reflection;
using MediatR;
using Microsoft.Extensions.DependencyInjection;

namespace Downstairs.Application;

public static class DependencyInjection
{
    public static IServiceCollection AddApplication(this IServiceCollection services)
    {
        // Add MediatR for CQRS
        services.AddMediatR(cfg => cfg.RegisterServicesFromAssembly(Assembly.GetExecutingAssembly()));

        return services;
    }
}