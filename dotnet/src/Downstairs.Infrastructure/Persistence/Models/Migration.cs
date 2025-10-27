namespace Downstairs.Infrastructure.Persistence.Models;

public partial class Migration
{
    public long Id { get; set; }

    public string Migration1 { get; set; } = null!;

    public int Batch { get; set; }
}