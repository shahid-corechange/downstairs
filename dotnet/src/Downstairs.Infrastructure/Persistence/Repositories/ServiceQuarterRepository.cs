using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class ServiceQuarterRepository(DownstairsDbContext context) : RepositoryBase<ServiceQuarter>(context)
{
}