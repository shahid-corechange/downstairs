using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class CountryRepository(DownstairsDbContext context) : RepositoryBase<Country>(context)
{
}