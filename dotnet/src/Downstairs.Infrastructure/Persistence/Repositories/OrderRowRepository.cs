using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class OrderRowRepository(DownstairsDbContext context) : RepositoryBase<OrderRow>(context)
{
}

