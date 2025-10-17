using TaskEntity = Downstairs.Infrastructure.Persistence.Models.Task;

namespace Downstairs.Infrastructure.Persistence.Repositories;

internal sealed class TaskRepository(DownstairsDbContext context) : RepositoryBase<TaskEntity>(context)
{
}