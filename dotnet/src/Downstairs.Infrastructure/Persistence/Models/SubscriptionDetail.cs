using System;
using System.Collections.Generic;

namespace Downstairs.Infrastructure.Persistence.Models;

public partial class SubscriptionDetail
{
    public long Id { get; set; }

    public int SubscriptionId { get; set; }

    public int Squarefeet { get; set; }

    public decimal PricePerQuarters { get; set; }

    public decimal PricePerSquarefeet { get; set; }

    public decimal PriceMaterial { get; set; }

    public decimal PriceEstablish { get; set; }

    public int VatId { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public DateTime? DeletedAt { get; set; }
}
