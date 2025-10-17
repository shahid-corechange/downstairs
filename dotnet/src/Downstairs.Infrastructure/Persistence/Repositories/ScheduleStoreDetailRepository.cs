using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class ScheduleStoreDetailRepository(DownstairsDbContext context) : RepositoryBase<ScheduleStoreDetail>(context)
{
}

