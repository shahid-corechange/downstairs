using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class FailedJobRepository(DownstairsDbContext context) : RepositoryBase<FailedJob>(context)
{
}

