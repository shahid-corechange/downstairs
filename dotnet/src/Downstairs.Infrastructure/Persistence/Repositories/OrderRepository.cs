using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class OrderRepository(DownstairsDbContext context) : RepositoryBase<Order>(context)
{
}