var builder = DistributedApplication.CreateBuilder(args);

// Add infrastructure services
var mysql = builder.AddMySql("mysql", port: 3306)
    .WithDataVolume()
    .WithLifetime(ContainerLifetime.Persistent);

var mysqldb = mysql.AddDatabase("downstairsdb");

// Add Azure Service Bus (using connection string from user secrets/config)
var serviceBus = builder.AddConnectionString("ServiceBus");

// Add core services
var apiGateway = builder.AddProject<Projects.Downstairs_ApiGateway>("api-gateway")
    .WithHttpHealthCheck("/health")
    .WithExternalHttpEndpoints();

var api = builder.AddProject<Projects.Downstairs_Api>("api")
    .WithReference(mysqldb)
    .WithReference(serviceBus)
    .WithHttpHealthCheck("/health");

var jobs = builder.AddProject<Projects.Downstairs_Jobs>("jobs")
    .WithReference(mysqldb)
    .WithReference(serviceBus)
    .WithHttpHealthCheck("/health");

var admin = builder.AddProject<Projects.Downstairs_Blazor_Admin>("admin")
    .WithReference(api)
    .WithHttpHealthCheck("/health");

var logPortal = builder.AddProject<Projects.Downstairs_LogPortal>("logs")
    .WithHttpHealthCheck("/health");

// Configure API Gateway routing references
apiGateway
    .WithReference(api)
    .WithReference(admin)
    .WithReference(logPortal);

// Wait for dependencies
api.WaitFor(mysqldb);
jobs.WaitFor(mysqldb).WaitFor(api);
admin.WaitFor(api);

builder.Build().Run();
