using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class ScheduleCleaningDeviationRepository(DownstairsDbContext context) : RepositoryBase<ScheduleCleaningDeviation>(context)
{
}

