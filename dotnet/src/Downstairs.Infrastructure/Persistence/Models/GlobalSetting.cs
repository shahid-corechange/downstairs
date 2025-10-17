namespace Downstairs.Infrastructure.Persistence.Models;

public partial class GlobalSetting
{
    public long Id { get; set; }

    public string Key { get; set; } = null!;

    public string Value { get; set; } = null!;

    public string Type { get; set; } = null!;

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }
}