using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class TeamUserRepository(DownstairsDbContext context) : RepositoryBase<TeamUser>(context)
{
}

