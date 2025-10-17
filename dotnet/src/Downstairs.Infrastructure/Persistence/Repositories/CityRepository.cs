using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class CityRepository(DownstairsDbContext context) : RepositoryBase<City>(context)
{
}

