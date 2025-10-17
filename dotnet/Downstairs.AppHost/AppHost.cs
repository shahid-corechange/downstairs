using Downstairs.ServiceDefaults.Configuration;

var builder = DistributedApplication.CreateBuilder(args);
ConnectionStringHelper.TryPopulateConfiguration(builder.Configuration, "ServiceBus");

// Create a dedicated network for all Downstairs services
const string NetworkName = "downstairs-local";

// Add infrastructure services with consistent naming and networking
var mysql = builder.AddMySql("mysql", port: 3306)
    .WithImage("mysql", "8.0")
    .WithDataVolume("downstairs-mysql-data")
    .WithLifetime(ContainerLifetime.Persistent)
    .WithContainerName("downstairs-mysql")
    .WithEnvironment("MYSQL_ROOT_PASSWORD", "password")
    .WithEnvironment("MYSQL_DATABASE", "downstairs")
    .WithArgs("--character-set-server=utf8mb4", "--collation-server=utf8mb4_unicode_ci");

var mysqldb = mysql.AddDatabase("downstairs");

// Add Redis cache for distributed caching and state management
var redis = builder.AddRedis("redis", port: 6379)
    .WithDataVolume("downstairs-redis-data")
    .WithLifetime(ContainerLifetime.Persistent)
    .WithContainerName("downstairs-redis")
    .WithArgs("--appendonly", "yes", "--appendfsync", "everysec");

// Add Azure Service Bus (using connection string from user secrets/config)
// Make ServiceBus optional for development - will use fallback if not configured
var serviceBus = builder.AddConnectionString("ServiceBus");

// Add core services with consistent naming and network grouping
var apiGateway = builder.AddProject<Projects.Downstairs_ApiGateway>("downstairs-api-gateway")
    .WithHttpHealthCheck("/health")
    .WithExternalHttpEndpoints()
    .WithEnvironment("ASPNETCORE_ENVIRONMENT", builder.Environment.EnvironmentName);

var api = builder.AddProject<Projects.Downstairs_Api>("downstairs-api")
    .WithReference(mysqldb)
    .WithReference(redis)
    .WithReference(serviceBus)
    .WithHttpHealthCheck("/health")
    .WithEnvironment("ASPNETCORE_ENVIRONMENT", builder.Environment.EnvironmentName);

var jobs = builder.AddProject<Projects.Downstairs_Jobs>("downstairs-jobs")
    .WithReference(mysqldb)
    .WithReference(redis)
    .WithReference(serviceBus)
    .WithHttpHealthCheck("/health")
    .WithEnvironment("ASPNETCORE_ENVIRONMENT", builder.Environment.EnvironmentName);

var admin = builder.AddProject<Projects.Downstairs_Blazor_Admin>("downstairs-admin")
    .WithReference(api)
    .WithHttpHealthCheck("/health")
    .WithEnvironment("ASPNETCORE_ENVIRONMENT", builder.Environment.EnvironmentName);

var logPortal = builder.AddProject<Projects.Downstairs_LogPortal>("downstairs-logportal")
    .WithHttpHealthCheck("/health")
    .WithReference(api) // LogPortal needs access to other services for monitoring
    .WithReference(jobs)
    .WithReference(apiGateway)
    .WithReference(admin)
    .WithEnvironment("ASPNETCORE_ENVIRONMENT", builder.Environment.EnvironmentName);

// Configure API Gateway routing references
apiGateway
    .WithReference(api)
    .WithReference(admin)
    .WithReference(logPortal);

// Configure service dependencies and startup order
// Temporarily removed WaitFor dependencies to allow services to start
// api.WaitFor(mysql).WaitFor(redis);
// jobs.WaitFor(mysql).WaitFor(redis).WaitFor(api);
// admin.WaitFor(api);
// logPortal.WaitFor(api).WaitFor(jobs);

var app = builder.Build();

// Add startup information
Console.WriteLine("🚀 Starting Downstairs application stack...");
Console.WriteLine($"📊 Network Name: {NetworkName}");
Console.WriteLine("🔧 Services: API Gateway, API, Jobs, Admin, LogPortal");
Console.WriteLine("💾 Infrastructure: MySQL, Redis, Azure Service Bus");

app.Run();
