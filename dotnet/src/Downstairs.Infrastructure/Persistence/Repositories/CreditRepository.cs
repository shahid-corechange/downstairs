using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class CreditRepository(DownstairsDbContext context) : RepositoryBase<Credit>(context)
{
}

