using ServiceDefaultsConstants = Downstairs.ServiceDefaults.Constants.DatabaseConstants;

namespace Downstairs.Infrastructure.Persistence.Constants;

/// <summary>
/// Database constants for Infrastructure persistence layer.
/// These are aliases to the ServiceDefaults constants to maintain consistency.
/// </summary>
public static class DatabaseConstants
{
    public static class Collations
    {
        /// <summary>
        /// Default Unicode collation for UTF-8 4-byte character set
        /// </summary>
        public const string Unicode = ServiceDefaultsConstants.Collations.Unicode;
    }

    public static class CharSets
    {
        /// <summary>
        /// UTF-8 4-byte character set (supports full Unicode including emojis)
        /// </summary>
        public const string Utf8mb4 = ServiceDefaultsConstants.CharSets.Utf8mb4;
    }
}