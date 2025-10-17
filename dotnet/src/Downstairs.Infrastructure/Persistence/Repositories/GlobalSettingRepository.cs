using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class GlobalSettingRepository(DownstairsDbContext context) : RepositoryBase<GlobalSetting>(context)
{
}

