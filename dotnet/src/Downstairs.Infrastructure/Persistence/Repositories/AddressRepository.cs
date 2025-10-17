using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class AddressRepository(DownstairsDbContext context) : RepositoryBase<Address>(context)
{
}

