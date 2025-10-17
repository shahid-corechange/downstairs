using System;
using System.Collections.Generic;

namespace Downstairs.Infrastructure.Persistence.Models;

public partial class Metum
{
    public uint Id { get; set; }

    public string MetableType { get; set; } = null!;

    public long MetableId { get; set; }

    public string Key { get; set; } = null!;

    public string? Value { get; set; }

    public string? Type { get; set; }

    public DateTime? PublishedAt { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }
}
