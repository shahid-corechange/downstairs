using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class UserOtpRepository(DownstairsDbContext context) : RepositoryBase<UserOtp>(context)
{
}