using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class ScheduleStoreRepository(DownstairsDbContext context) : RepositoryBase<ScheduleStore>(context)
{
}

