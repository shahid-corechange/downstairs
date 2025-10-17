namespace Downstairs.Application.Common.Constants;

/// <summary>
/// Constants for cache keys used throughout the application
/// </summary>
public static class CacheKeys
{
    // Customer cache keys
    public const string AllCustomers = "customers:all";
    public const string CustomerById = "customer:id:{0}";
    public const string CustomerByOrgNumber = "customer:org:{0}";

    // Invoice cache keys
    public const string AllInvoices = "invoices:all";
    public const string InvoiceById = "invoice:id:{0}";
    public const string InvoicesByCustomer = "invoices:customer:{0}";
    public const string InvoiceByNumber = "invoice:number:{0}";

    // Cache patterns for invalidation
    public const string CustomerPattern = "customer:*";
    public const string InvoicePattern = "invoice:*";
    public const string InvoicesByCustomerPattern = "invoices:customer:{0}";

    // Cache expiry times
    public static readonly TimeSpan ShortCacheDuration = TimeSpan.FromMinutes(5);
    public static readonly TimeSpan MediumCacheDuration = TimeSpan.FromMinutes(10);
    public static readonly TimeSpan LongCacheDuration = TimeSpan.FromMinutes(30);

    /// <summary>
    /// Formats a cache key with parameters
    /// </summary>
    public static string Format(string keyTemplate, params object[] args)
    {
        return string.Format(keyTemplate, args);
    }
}