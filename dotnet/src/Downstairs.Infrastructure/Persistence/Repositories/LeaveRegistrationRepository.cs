using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class LeaveRegistrationRepository(DownstairsDbContext context) : RepositoryBase<LeaveRegistration>(context)
{
}