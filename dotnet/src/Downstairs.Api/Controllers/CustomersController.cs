using Dapr;
using Downstairs.Application.Commands.Customers;
using Downstairs.Application.Queries.Customers;
using MediatR;
using Microsoft.AspNetCore.Mvc;

namespace Downstairs.Api.Controllers;

/// <summary>
/// Controller for managing customers
/// </summary>
[ApiController]
[Route("api/[controller]")]
public class CustomersController : ControllerBase
{
    private readonly IMediator _mediator;
    private readonly ILogger<CustomersController> _logger;

    public CustomersController(IMediator mediator, ILogger<CustomersController> logger)
    {
        _mediator = mediator;
        _logger = logger;
    }

    /// <summary>
    /// Get all customers
    /// </summary>
    [HttpGet]
    [ProducesResponseType(typeof(IEnumerable<CustomerDto>), StatusCodes.Status200OK)]
    public async Task<ActionResult<IEnumerable<CustomerDto>>> GetCustomers()
    {
        _logger.LogInformation("Getting all customers");
        
        var query = new GetCustomersQuery();
        var customers = await _mediator.Send(query);
        
        return Ok(customers);
    }

    /// <summary>
    /// Get customer by ID
    /// </summary>
    [HttpGet("{id:long}")]
    [ProducesResponseType(typeof(CustomerDto), StatusCodes.Status200OK)]
    [ProducesResponseType(StatusCodes.Status404NotFound)]
    public async Task<ActionResult<CustomerDto>> GetCustomer(long id)
    {
        _logger.LogInformation("Getting customer with ID: {CustomerId}", id);
        
        var query = new GetCustomerByIdQuery(id);
        var customer = await _mediator.Send(query);
        
        if (customer == null)
        {
            return NotFound();
        }
        
        return Ok(customer);
    }

    /// <summary>
    /// Create a new customer
    /// </summary>
    [HttpPost]
    [ProducesResponseType(typeof(long), StatusCodes.Status201Created)]
    [ProducesResponseType(StatusCodes.Status400BadRequest)]
    public async Task<ActionResult<long>> CreateCustomer([FromBody] CreateCustomerRequest request)
    {
        _logger.LogInformation("Creating new customer: {CustomerName}", request.Name);
        
        var command = new CreateCustomerCommand(
            request.Name,
            request.Email,
            request.OrganizationNumber,
            request.Phone,
            request.Street,
            request.City,
            request.PostalCode,
            request.Country);
        
        try
        {
            var customerId = await _mediator.Send(command);
            return CreatedAtAction(nameof(GetCustomer), new { id = customerId }, customerId);
        }
        catch (InvalidOperationException ex)
        {
            _logger.LogWarning("Failed to create customer: {Error}", ex.Message);
            return BadRequest(ex.Message);
        }
    }

    /// <summary>
    /// Subscribe to customer events via Dapr
    /// </summary>
    [HttpPost("events/customer-created")]
    [Topic("pubsub-servicebus", "CustomerCreatedEvent")]
    public async Task<IActionResult> HandleCustomerCreated([FromBody] CustomerCreatedEventDto eventDto)
    {
        _logger.LogInformation("Received CustomerCreated event for customer {CustomerId}", eventDto.CustomerId);
        
        // Handle the event (e.g., send welcome email, update analytics, etc.)
        // This is where you'd implement any side effects of customer creation
        
        return Ok();
    }
}

/// <summary>
/// Request model for creating a customer
/// </summary>
public record CreateCustomerRequest(
    string Name,
    string Email,
    string OrganizationNumber,
    string Phone,
    string Street,
    string City,
    string PostalCode,
    string Country);

/// <summary>
/// Event DTO for customer created events
/// </summary>
public record CustomerCreatedEventDto(
    long CustomerId,
    string Name,
    string Email,
    string OrganizationNumber);