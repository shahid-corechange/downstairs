using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class BlockDayRepository(DownstairsDbContext context) : RepositoryBase<BlockDay>(context)
{
}