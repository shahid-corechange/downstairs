namespace Downstairs.Infrastructure.Persistence.Models;

public partial class BlindIndex
{
    public long MyRowId { get; set; }

    public string IndexableType { get; set; } = null!;

    public long IndexableId { get; set; }

    public string Name { get; set; } = null!;

    public string Value { get; set; } = null!;
}