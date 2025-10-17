using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class ScheduleCleaningRepository(DownstairsDbContext context) : RepositoryBase<ScheduleCleaning>(context)
{
}