using System.Collections.Concurrent;
using Microsoft.Extensions.Configuration;

namespace Downstairs.ServiceDefaults.Configuration;

/// <summary>
/// Provides helpers for resolving connection strings from environment variables or .env files.
/// </summary>
public static class ConnectionStringHelper
{
    private static readonly ConcurrentDictionary<string, string?> Cache = new(StringComparer.OrdinalIgnoreCase);
    private static readonly Lazy<IDictionary<string, string>> DotEnvValues = new(LoadDotEnvValues, isThreadSafe: true);

    /// <summary>
    /// Resolves the connection string with the given name.
    /// </summary>
    public static string? Resolve(string connectionName)
    {
        if (string.IsNullOrWhiteSpace(connectionName))
        {
            return null;
        }

        return Cache.GetOrAdd(connectionName, ResolveCore);
    }

    /// <summary>
    /// Ensures the connection string exists in the provided configuration by resolving it
    /// from environment variables or the project-level .env file.
    /// </summary>
    public static bool TryPopulateConfiguration(IConfiguration configuration, string connectionName)
    {
        if (configuration is null)
        {
            throw new ArgumentNullException(nameof(configuration));
        }

        if (string.IsNullOrWhiteSpace(connectionName))
        {
            return false;
        }

        var existing = configuration.GetConnectionString(connectionName);
        if (!string.IsNullOrWhiteSpace(existing))
        {
            return true;
        }

        var resolved = Resolve(connectionName);
        if (string.IsNullOrWhiteSpace(resolved))
        {
            return false;
        }

        configuration[$"ConnectionStrings:{connectionName}"] = resolved;
        return true;
    }

    /// <summary>
    /// Retrieves the required connection string, attempting to populate the configuration if needed. Throws if not found.
    /// </summary>
    public static string GetRequiredConnectionString(IConfiguration configuration, string connectionName)
    {
        if (configuration is null)
        {
            throw new ArgumentNullException(nameof(configuration));
        }

        if (string.IsNullOrWhiteSpace(connectionName))
        {
            throw new ArgumentException("Connection name is required.", nameof(connectionName));
        }

        if (!TryPopulateConfiguration(configuration, connectionName))
        {
            throw new InvalidOperationException($"Connection string '{connectionName}' is not configured. Please set it via configuration or environment variables.");
        }

        return configuration.GetConnectionString(connectionName)!;
    }

    private static string? ResolveCore(string connectionName)
    {
        var environmentKey = $"ConnectionStrings__{connectionName}";
        var fromEnvironment = Environment.GetEnvironmentVariable(environmentKey);
        if (!string.IsNullOrWhiteSpace(fromEnvironment))
        {
            return fromEnvironment;
        }

        if (DotEnvValues.Value.TryGetValue(environmentKey, out var fromEnvFile) &&
            !string.IsNullOrWhiteSpace(fromEnvFile))
        {
            return fromEnvFile;
        }

        return null;
    }

    private static IDictionary<string, string> LoadDotEnvValues()
    {
        var values = new Dictionary<string, string>(StringComparer.OrdinalIgnoreCase);
        var envPath = LocateEnvFile();
        if (envPath is null)
        {
            return values;
        }

        foreach (var rawLine in File.ReadAllLines(envPath))
        {
            var line = rawLine.Trim();
            if (line.Length == 0 || line.StartsWith("#", StringComparison.Ordinal))
            {
                continue;
            }

            var separatorIndex = line.IndexOf('=');
            if (separatorIndex <= 0)
            {
                continue;
            }

            var key = line[..separatorIndex].Trim();
            if (string.IsNullOrEmpty(key))
            {
                continue;
            }

            var value = line[(separatorIndex + 1)..].Trim().Trim('"');
            if (value.Length == 0)
            {
                continue;
            }

            values[key] = value;
        }

        return values;
    }

    private static string? LocateEnvFile()
    {
        var current = AppContext.BaseDirectory;
        while (!string.IsNullOrEmpty(current))
        {
            var candidate = Path.Combine(current, ".env");
            if (File.Exists(candidate))
            {
                return candidate;
            }

            var parent = Directory.GetParent(current)?.FullName;
            if (parent is null || string.Equals(parent, current, StringComparison.Ordinal))
            {
                break;
            }

            current = parent;
        }

        return null;
    }
}