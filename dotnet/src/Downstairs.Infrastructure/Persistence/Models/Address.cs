namespace Downstairs.Infrastructure.Persistence.Models;

public partial class Address
{
    public long Id { get; set; }

    public long CityId { get; set; }

    public string Address1 { get; set; } = null!;

    public string? Address2 { get; set; }

    public string? Area { get; set; }

    public string PostalCode { get; set; } = null!;

    public decimal? Accuracy { get; set; }

    public decimal? Latitude { get; set; }

    public decimal? Longitude { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public DateTime? DeletedAt { get; set; }

    public virtual City City { get; set; } = null!;

    public virtual ICollection<Customer> Customers { get; set; } = new List<Customer>();

    public virtual ICollection<Employee> Employees { get; set; } = new List<Employee>();

    public virtual ICollection<Property> Properties { get; set; } = new List<Property>();

    public virtual ICollection<Store> Stores { get; set; } = new List<Store>();
}