namespace Downstairs.ServiceDefaults.Constants;

public static class DatabaseConstants
{
    public static class Collations
    {
        /// <summary>
        /// Default Unicode collation for UTF-8 4-byte character set
        /// </summary>
        public const string Unicode = "utf8mb4_unicode_ci";
    }

    public static class CharSets
    {
        /// <summary>
        /// UTF-8 4-byte character set (supports full Unicode including emojis)
        /// </summary>
        public const string Utf8mb4 = "utf8mb4";
    }
}