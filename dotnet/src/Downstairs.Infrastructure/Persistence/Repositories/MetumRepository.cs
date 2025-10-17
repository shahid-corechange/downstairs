using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class MetumRepository(DownstairsDbContext context) : RepositoryBase<Metum>(context)
{
}