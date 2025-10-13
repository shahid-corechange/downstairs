using MediatR;

namespace Downstairs.Application.Common.Interfaces;

/// <summary>
/// Marker interface for commands (write operations)
/// </summary>
public interface ICommand : IRequest
{
}

/// <summary>
/// Marker interface for commands with return values
/// </summary>
/// <typeparam name="TResult">The return type</typeparam>
public interface ICommand<out TResult> : IRequest<TResult>
{
}

/// <summary>
/// Interface for command handlers
/// </summary>
/// <typeparam name="TCommand">The command type</typeparam>
public interface ICommandHandler<in TCommand> : IRequestHandler<TCommand>
    where TCommand : ICommand
{
}

/// <summary>
/// Interface for command handlers with return values
/// </summary>
/// <typeparam name="TCommand">The command type</typeparam>
/// <typeparam name="TResult">The return type</typeparam>
public interface ICommandHandler<in TCommand, TResult> : IRequestHandler<TCommand, TResult>
    where TCommand : ICommand<TResult>
{
}