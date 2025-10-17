using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class UserInfoRepository(DownstairsDbContext context) : RepositoryBase<UserInfo>(context)
{
}

