using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class MonthlyWorkHourRepository(DownstairsDbContext context) : RepositoryBase<MonthlyWorkHour>(context)
{
}

