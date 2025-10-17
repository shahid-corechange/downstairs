using Downstairs.Infrastructure.Persistence.Models;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class EmployeeRepository(DownstairsDbContext context) : RepositoryBase<Employee>(context)
{
}

