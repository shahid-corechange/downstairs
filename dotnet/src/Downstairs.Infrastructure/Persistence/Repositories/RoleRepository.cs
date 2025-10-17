using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class RoleRepository(DownstairsDbContext context) : RepositoryBase<Role>(context)
{
}

