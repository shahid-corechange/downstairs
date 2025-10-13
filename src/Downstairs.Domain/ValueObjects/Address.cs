using Downstairs.Domain.Shared;

namespace Downstairs.Domain.ValueObjects;

/// <summary>
/// Address value object representing postal addresses
/// </summary>
public record Address : ValueObject
{
    public string Street { get; init; } = string.Empty;
    public string City { get; init; } = string.Empty;
    public string PostalCode { get; init; } = string.Empty;
    public string Country { get; init; } = string.Empty;

    public Address() { }

    public Address(string street, string city, string postalCode, string country)
    {
        Street = street;
        City = city;
        PostalCode = postalCode;
        Country = country;
    }

    public override string ToString()
    {
        return $"{Street}, {City} {PostalCode}, {Country}";
    }
}