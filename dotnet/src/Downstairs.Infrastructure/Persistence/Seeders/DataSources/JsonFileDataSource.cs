using System.Text.Json;
using Downstairs.Infrastructure.Persistence.Seeders.Interfaces;

namespace Downstairs.Infrastructure.Persistence.Seeders.DataSources;

/// <summary>
/// Data source implementation for JSON files
/// Loads seeder data from JSON files (e.g., countries.json, global_settings.json)
/// </summary>
/// <typeparam name="T">Entity type to load from JSON</typeparam>
public class JsonFileDataSource<T> : ISeederDataSource<T> where T : class
{
    private readonly string _filePath;

    public string SourceName => $"JSON File: {Path.GetFileName(_filePath)}";

    public JsonFileDataSource(string filePath)
    {
        _filePath = filePath ?? throw new ArgumentNullException(nameof(filePath));
    }

    public async Task<IEnumerable<T>> GetDataAsync()
    {
        if (!await IsAvailableAsync())
        {
            return Enumerable.Empty<T>();
        }

        try
        {
            var json = await File.ReadAllTextAsync(_filePath);

            var options = new JsonSerializerOptions
            {
                PropertyNamingPolicy = JsonNamingPolicy.CamelCase,
                PropertyNameCaseInsensitive = true
            };

            var data = JsonSerializer.Deserialize<T[]>(json, options);
            return data ?? Enumerable.Empty<T>();
        }
        catch (JsonException ex)
        {
            throw new InvalidOperationException($"Failed to deserialize JSON file {_filePath}", ex);
        }
        catch (Exception ex)
        {
            throw new InvalidOperationException($"Failed to read JSON file {_filePath}", ex);
        }
    }

    public async Task<bool> IsAvailableAsync()
    {
        return await Task.FromResult(File.Exists(_filePath));
    }
}