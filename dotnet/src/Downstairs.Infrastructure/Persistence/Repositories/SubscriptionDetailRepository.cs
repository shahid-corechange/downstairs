using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class SubscriptionDetailRepository(DownstairsDbContext context) : RepositoryBase<SubscriptionDetail>(context)
{
}