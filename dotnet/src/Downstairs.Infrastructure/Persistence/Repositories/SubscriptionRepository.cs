using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class SubscriptionRepository(DownstairsDbContext context) : RepositoryBase<Subscription>(context)
{
}