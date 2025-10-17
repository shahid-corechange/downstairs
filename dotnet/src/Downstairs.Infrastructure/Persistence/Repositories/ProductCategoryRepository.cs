using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class ProductCategoryRepository(DownstairsDbContext context) : RepositoryBase<ProductCategory>(context)
{
}

