using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class ActivityLogRepository(DownstairsDbContext context) : RepositoryBase<ActivityLog>(context)
{
}

