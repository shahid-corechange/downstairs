using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class PropertyTypeRepository(DownstairsDbContext context) : RepositoryBase<PropertyType>(context)
{
}