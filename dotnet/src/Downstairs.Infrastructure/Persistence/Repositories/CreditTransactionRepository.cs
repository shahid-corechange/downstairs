using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class CreditTransactionRepository(DownstairsDbContext context) : RepositoryBase<CreditTransaction>(context)
{
}

