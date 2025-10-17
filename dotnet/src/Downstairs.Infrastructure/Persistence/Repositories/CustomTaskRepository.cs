using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class CustomTaskRepository(DownstairsDbContext context) : RepositoryBase<CustomTask>(context)
{
}