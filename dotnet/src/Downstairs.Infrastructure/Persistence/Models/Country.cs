namespace Downstairs.Infrastructure.Persistence.Models;

public partial class Country
{
    public long Id { get; set; }

    public string Code { get; set; } = null!;

    public string Name { get; set; } = null!;

    public string Currency { get; set; } = null!;

    public string DialCode { get; set; } = null!;

    public string Flag { get; set; } = null!;

    public virtual ICollection<City> Cities { get; set; } = new List<City>();
}