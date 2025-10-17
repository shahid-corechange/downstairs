namespace Downstairs.Domain.Shared;

/// <summary>
/// Domain constants used throughout the application
/// </summary>
public static class DomainConstants
{
    /// <summary>
    /// Currency constants
    /// </summary>
    public static class Currency
    {
        /// <summary>
        /// Swedish Krona currency code
        /// </summary>
        public const string SEK = "SEK";

        /// <summary>
        /// Default currency for the application
        /// </summary>
        public const string Default = SEK;
    }

    /// <summary>
    /// Invoice-related constants
    /// </summary>
    public static class Invoice
    {
        /// <summary>
        /// Default number of days for invoice due date
        /// </summary>
        public const int DefaultDueDays = 30;
    }
}