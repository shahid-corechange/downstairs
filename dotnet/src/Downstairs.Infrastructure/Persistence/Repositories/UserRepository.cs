using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class UserRepository(DownstairsDbContext context) : RepositoryBase<User>(context)
{
}