using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class WorkHourRepository(DownstairsDbContext context) : RepositoryBase<WorkHour>(context)
{
}

