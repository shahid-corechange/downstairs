using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class KeyPlaceRepository(DownstairsDbContext context) : RepositoryBase<KeyPlace>(context)
{
}

