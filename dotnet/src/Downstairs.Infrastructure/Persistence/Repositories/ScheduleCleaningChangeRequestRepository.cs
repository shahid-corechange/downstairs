using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class ScheduleCleaningChangeRequestRepository(DownstairsDbContext context) : RepositoryBase<ScheduleCleaningChangeRequest>(context)
{
}