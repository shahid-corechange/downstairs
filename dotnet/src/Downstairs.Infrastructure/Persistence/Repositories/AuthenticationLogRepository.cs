using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class AuthenticationLogRepository(DownstairsDbContext context) : RepositoryBase<AuthenticationLog>(context)
{
}