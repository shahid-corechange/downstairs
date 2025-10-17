using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class ScheduleCleaningTaskRepository(DownstairsDbContext context) : RepositoryBase<ScheduleCleaningTask>(context)
{
}