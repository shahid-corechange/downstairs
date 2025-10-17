using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class ModelHasPermissionRepository(DownstairsDbContext context) : RepositoryBase<ModelHasPermission>(context)
{
}