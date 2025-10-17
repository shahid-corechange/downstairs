using Downstairs.LogPortal.BackgroundServices;
using Downstairs.LogPortal.Hubs;
using Downstairs.LogPortal.Services;

var builder = WebApplication.CreateBuilder(args);

builder.AddServiceDefaults();

// Add services to the container.
builder.Services.AddRazorPages();
builder.Services.AddServerSideBlazor();

// Add SignalR for real-time updates
builder.Services.AddSignalR();

// Register LogPortal services
builder.Services.AddScoped<ILogService, LogService>();
builder.Services.AddScoped<IMetricsService, MetricsService>();
builder.Services.AddScoped<IHealthCheckService, HealthCheckService>();
builder.Services.AddScoped<IAlertService, AlertService>();
builder.Services.AddScoped<IDashboardNotificationService, DashboardNotificationService>();

// Add HTTP client for health checks
builder.Services.AddHttpClient<IHealthCheckService, HealthCheckService>();

// Add background service for periodic dashboard updates
builder.Services.AddHostedService<DashboardRefreshService>();

var app = builder.Build();

app.MapDefaultEndpoints();

// Configure the HTTP request pipeline.
if (!app.Environment.IsDevelopment())
{
    app.UseExceptionHandler("/Error");
    // The default HSTS value is 30 days. You may want to change this for production scenarios, see https://aka.ms/aspnetcore-hsts.
    app.UseHsts();
}

app.UseHttpsRedirection();
app.UseStaticFiles();
app.UseRouting();

// Map SignalR hub
app.MapHub<DashboardHub>("/dashboardHub");

app.MapBlazorHub();
app.MapFallbackToPage("/_Host");

app.Run();