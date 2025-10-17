using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class ScheduleEmployeeRepository(DownstairsDbContext context) : RepositoryBase<ScheduleEmployee>(context)
{
}