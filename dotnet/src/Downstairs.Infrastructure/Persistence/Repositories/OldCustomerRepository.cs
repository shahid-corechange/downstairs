using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class OldCustomerRepository(DownstairsDbContext context) : RepositoryBase<OldCustomer>(context)
{
}

