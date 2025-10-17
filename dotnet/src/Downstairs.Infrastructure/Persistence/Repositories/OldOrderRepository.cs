using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class OldOrderRepository(DownstairsDbContext context) : RepositoryBase<OldOrder>(context)
{
}