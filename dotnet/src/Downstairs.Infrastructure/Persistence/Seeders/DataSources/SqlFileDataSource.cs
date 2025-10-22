using Downstairs.Infrastructure.Persistence.Seeders.Interfaces;
using Microsoft.EntityFrameworkCore;

namespace Downstairs.Infrastructure.Persistence.Seeders.DataSources;

/// <summary>
/// Data source implementation for SQL files
/// Executes SQL files directly against the database (e.g., cities.sql with 84K+ records)
/// </summary>
/// <typeparam name="T">Entity type affected by the SQL file</typeparam>
public class SqlFileDataSource<T> : ISeederDataSource<T> where T : class
{
    private readonly string _filePath;
    private readonly DownstairsDbContext _context;

    public string SourceName => $"SQL File: {Path.GetFileName(_filePath)}";

    public SqlFileDataSource(string filePath, DownstairsDbContext context)
    {
        _filePath = filePath ?? throw new ArgumentNullException(nameof(filePath));
        _context = context ?? throw new ArgumentNullException(nameof(context));
    }

    public async Task<IEnumerable<T>> GetDataAsync()
    {
        if (!await IsAvailableAsync())
        {
            return Enumerable.Empty<T>();
        }

        try
        {
            var sql = await File.ReadAllTextAsync(_filePath);

            if (string.IsNullOrWhiteSpace(sql))
            {
                return Enumerable.Empty<T>();
            }

            // Execute the SQL file
            await _context.Database.ExecuteSqlRawAsync(sql);

            // Return the seeded data for verification (limit to first 1000 records for performance)
            return await _context.Set<T>().Take(1000).ToListAsync();
        }
        catch (Exception ex)
        {
            throw new InvalidOperationException($"Failed to execute SQL file {_filePath}", ex);
        }
    }

    public async Task<bool> IsAvailableAsync()
    {
        return await Task.FromResult(File.Exists(_filePath));
    }
}