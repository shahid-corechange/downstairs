namespace Downstairs.Infrastructure.Persistence.Models;

public partial class City
{
    public long Id { get; set; }

    public long CountryId { get; set; }

    public string Name { get; set; } = null!;

    public virtual ICollection<Address> Addresses { get; set; } = new List<Address>();

    public virtual Country Country { get; set; } = null!;
}