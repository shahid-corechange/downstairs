using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class FixedPriceRowRepository(DownstairsDbContext context) : RepositoryBase<FixedPriceRow>(context)
{
}

