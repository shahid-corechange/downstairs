using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class ServiceRepository(DownstairsDbContext context) : RepositoryBase<Service>(context)
{
}