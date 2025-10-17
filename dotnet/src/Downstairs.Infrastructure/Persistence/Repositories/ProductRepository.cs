using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class ProductRepository(DownstairsDbContext context) : RepositoryBase<Product>(context)
{
}