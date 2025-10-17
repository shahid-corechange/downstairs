using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class SubscriptionProductRepository(DownstairsDbContext context) : RepositoryBase<SubscriptionProduct>(context)
{
}

