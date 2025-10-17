using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class OrderFixedPriceRepository(DownstairsDbContext context) : RepositoryBase<OrderFixedPrice>(context)
{
}