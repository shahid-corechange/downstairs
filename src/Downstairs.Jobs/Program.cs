using Downstairs.Application;
using Downstairs.Infrastructure;
using Downstairs.Jobs.Jobs;
using Quartz;

var builder = WebApplication.CreateBuilder(args);

// Add Aspire service defaults
builder.AddServiceDefaults();

// Add services to the container
builder.Services.AddControllers().AddDapr();

// Add Application and Infrastructure layers
builder.Services.AddApplication();
builder.Services.AddInfrastructure(builder.Configuration);

// Configure Quartz.NET
builder.Services.AddQuartz(q =>
{
    
    // Configure job store (in-memory for this demo)
    q.UseInMemoryStore();
    
    // Configure jobs and triggers
    
    // 01:00 - Create dummy customer in Fortnox
    var createCustomerJobKey = new JobKey("CreateFortnoxCustomer");
    q.AddJob<CreateFortnoxCustomerJob>(opts => opts.WithIdentity(createCustomerJobKey));
    q.AddTrigger(opts => opts
        .ForJob(createCustomerJobKey)
        .WithIdentity("CreateFortnoxCustomer-trigger")
        .WithCronSchedule("0 0 1 * * ?") // Daily at 01:00
        .WithDescription("Create dummy customer in Fortnox daily at 01:00"));
    
    // 02:00 - Create dummy invoice in Fortnox  
    var createInvoiceJobKey = new JobKey("CreateFortnoxInvoice");
    q.AddJob<CreateFortnoxInvoiceJob>(opts => opts.WithIdentity(createInvoiceJobKey));
    q.AddTrigger(opts => opts
        .ForJob(createInvoiceJobKey)
        .WithIdentity("CreateFortnoxInvoice-trigger")
        .WithCronSchedule("0 0 2 * * ?") // Daily at 02:00
        .WithDescription("Create dummy invoice in Fortnox daily at 02:00"));
    
    // 03:00 - Send invoices to Kivra
    var sendToKivraJobKey = new JobKey("SendInvoicesToKivra");
    q.AddJob<SendInvoicesToKivraJob>(opts => opts.WithIdentity(sendToKivraJobKey));
    q.AddTrigger(opts => opts
        .ForJob(sendToKivraJobKey)
        .WithIdentity("SendInvoicesToKivra-trigger")
        .WithCronSchedule("0 0 3 * * ?") // Daily at 03:00
        .WithDescription("Send pending invoices to Kivra daily at 03:00"));
        
    // Add immediate trigger for testing (runs 30 seconds after startup)
    q.AddTrigger(opts => opts
        .ForJob(createCustomerJobKey)
        .WithIdentity("CreateFortnoxCustomer-startup")
        .StartAt(DateTimeOffset.Now.AddSeconds(30))
        .WithDescription("Test trigger - create customer 30 seconds after startup"));
});

// Add Quartz hosted service
builder.Services.AddQuartzHostedService(q => q.WaitForJobsToComplete = true);

// Add health checks
builder.Services.AddHealthChecks();

var app = builder.Build();

// Map Aspire service defaults  
app.MapDefaultEndpoints();

// Configure the HTTP request pipeline
app.UseRouting();

// Subscribe to Dapr pub/sub
app.UseCloudEvents();
app.MapSubscribeHandler();

app.MapControllers();

// Add status endpoint
app.MapGet("/", () => new
{
    Service = "Downstairs Jobs Service",
    Status = "Running",
    StartTime = DateTime.UtcNow,
    Jobs = new[]
    {
        new { Name = "CreateFortnoxCustomer", Schedule = "Daily at 01:00", Description = "Create dummy customer in Fortnox" },
        new { Name = "CreateFortnoxInvoice", Schedule = "Daily at 02:00", Description = "Create dummy invoice in Fortnox" },
        new { Name = "SendInvoicesToKivra", Schedule = "Daily at 03:00", Description = "Send pending invoices to Kivra" }
    }
});

app.Run();
