using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class FixedPriceRepository(DownstairsDbContext context) : RepositoryBase<FixedPrice>(context)
{
}