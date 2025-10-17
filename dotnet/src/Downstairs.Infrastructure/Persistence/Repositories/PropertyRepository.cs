using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class PropertyRepository(DownstairsDbContext context) : RepositoryBase<Property>(context)
{
}