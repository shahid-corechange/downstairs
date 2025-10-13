using MediatR;

namespace Downstairs.Application.Common.Interfaces;

/// <summary>
/// Marker interface for queries (read operations)
/// </summary>
/// <typeparam name="TResult">The return type</typeparam>
public interface IQuery<out TResult> : IRequest<TResult>
{
}

/// <summary>
/// Interface for query handlers
/// </summary>
/// <typeparam name="TQuery">The query type</typeparam>
/// <typeparam name="TResult">The return type</typeparam>
public interface IQueryHandler<in TQuery, TResult> : IRequestHandler<TQuery, TResult>
    where TQuery : IQuery<TResult>
{
}