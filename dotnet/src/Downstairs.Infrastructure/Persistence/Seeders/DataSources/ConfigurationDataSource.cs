using Downstairs.Infrastructure.Persistence.Seeders.Interfaces;
using Microsoft.Extensions.Configuration;

namespace Downstairs.Infrastructure.Persistence.Seeders.DataSources;

/// <summary>
/// Data source implementation for configuration-based data
/// Loads seeder data from application configuration (appsettings.json)
/// </summary>
/// <typeparam name="T">Entity type to load from configuration</typeparam>
public class ConfigurationDataSource<T> : ISeederDataSource<T> where T : class
{
    private readonly IConfiguration _configuration;
    private readonly string _configurationSection;

    public string SourceName => $"Configuration: {_configurationSection}";

    public ConfigurationDataSource(IConfiguration configuration, string configurationSection)
    {
        _configuration = configuration ?? throw new ArgumentNullException(nameof(configuration));
        _configurationSection = configurationSection ?? throw new ArgumentNullException(nameof(configurationSection));
    }

    public async Task<IEnumerable<T>> GetDataAsync()
    {
        if (!await IsAvailableAsync())
        {
            return Enumerable.Empty<T>();
        }

        try
        {
            var data = _configuration.GetSection(_configurationSection).Get<T[]>();
            return data ?? Enumerable.Empty<T>();
        }
        catch (Exception ex)
        {
            throw new InvalidOperationException($"Failed to load configuration section {_configurationSection}", ex);
        }
    }

    public async Task<bool> IsAvailableAsync()
    {
        return await Task.FromResult(_configuration.GetSection(_configurationSection).Exists());
    }
}