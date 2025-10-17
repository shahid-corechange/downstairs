using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class BlindIndexRepository(DownstairsDbContext context) : RepositoryBase<BlindIndex>(context)
{
}