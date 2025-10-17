using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class PriceAdjustmentRowRepository(DownstairsDbContext context) : RepositoryBase<PriceAdjustmentRow>(context)
{
}