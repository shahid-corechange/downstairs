using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class DeviationRepository(DownstairsDbContext context) : RepositoryBase<Deviation>(context)
{
}

