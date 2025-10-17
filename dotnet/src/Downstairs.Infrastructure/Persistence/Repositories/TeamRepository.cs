using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class TeamRepository(DownstairsDbContext context) : RepositoryBase<Team>(context)
{
}

