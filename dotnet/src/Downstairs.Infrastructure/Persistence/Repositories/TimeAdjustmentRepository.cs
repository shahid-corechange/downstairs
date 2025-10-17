using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class TimeAdjustmentRepository(DownstairsDbContext context) : RepositoryBase<TimeAdjustment>(context)
{
}