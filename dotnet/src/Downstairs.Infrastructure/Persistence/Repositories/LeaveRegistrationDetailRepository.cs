using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class LeaveRegistrationDetailRepository(DownstairsDbContext context) : RepositoryBase<LeaveRegistrationDetail>(context)
{
}