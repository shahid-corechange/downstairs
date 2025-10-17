namespace Downstairs.Infrastructure.Persistence.Models;

public partial class Translation
{
    public long Id { get; set; }

    public string TranslationableType { get; set; } = null!;

    public long TranslationableId { get; set; }

    public string Key { get; set; } = null!;

    public string? EnUs { get; set; }

    public string? NnNo { get; set; }

    public string? SvSe { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public DateTime? DeletedAt { get; set; }
}