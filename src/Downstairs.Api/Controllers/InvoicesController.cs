using Dapr;
using Downstairs.Application.Commands.Invoices;
using Downstairs.Application.Queries.Invoices;
using MediatR;
using Microsoft.AspNetCore.Mvc;

namespace Downstairs.Api.Controllers;

/// <summary>
/// Controller for managing invoices
/// </summary>
[ApiController]
[Route("api/[controller]")]
public class InvoicesController : ControllerBase
{
    private readonly IMediator _mediator;
    private readonly ILogger<InvoicesController> _logger;

    public InvoicesController(IMediator mediator, ILogger<InvoicesController> logger)
    {
        _mediator = mediator;
        _logger = logger;
    }

    /// <summary>
    /// Get all invoices
    /// </summary>
    [HttpGet]
    [ProducesResponseType(typeof(IEnumerable<InvoiceDto>), StatusCodes.Status200OK)]
    public async Task<ActionResult<IEnumerable<InvoiceDto>>> GetInvoices()
    {
        _logger.LogInformation("Getting all invoices");
        
        var query = new GetInvoicesQuery();
        var invoices = await _mediator.Send(query);
        
        return Ok(invoices);
    }

    /// <summary>
    /// Get invoices by customer ID
    /// </summary>
    [HttpGet("customer/{customerId:guid}")]
    [ProducesResponseType(typeof(IEnumerable<InvoiceDto>), StatusCodes.Status200OK)]
    public async Task<ActionResult<IEnumerable<InvoiceDto>>> GetInvoicesByCustomer(Guid customerId)
    {
        _logger.LogInformation("Getting invoices for customer: {CustomerId}", customerId);
        
        var query = new GetInvoicesByCustomerQuery(customerId);
        var invoices = await _mediator.Send(query);
        
        return Ok(invoices);
    }

    /// <summary>
    /// Create a new invoice
    /// </summary>
    [HttpPost]
    [ProducesResponseType(typeof(Guid), StatusCodes.Status201Created)]
    [ProducesResponseType(StatusCodes.Status400BadRequest)]
    public async Task<ActionResult<Guid>> CreateInvoice([FromBody] CreateInvoiceRequest request)
    {
        _logger.LogInformation("Creating new invoice for customer: {CustomerId}", request.CustomerId);
        
        var command = new CreateInvoiceCommand(
            request.CustomerId,
            request.InvoiceNumber,
            request.TotalAmount,
            request.Currency,
            request.InvoiceDate,
            request.DueDate,
            request.Lines);
        
        try
        {
            var invoiceId = await _mediator.Send(command);
            return CreatedAtAction(nameof(GetInvoice), new { id = invoiceId }, invoiceId);
        }
        catch (InvalidOperationException ex)
        {
            _logger.LogWarning("Failed to create invoice: {Error}", ex.Message);
            return BadRequest(ex.Message);
        }
    }

    /// <summary>
    /// Get invoice by ID
    /// </summary>
    [HttpGet("{id:guid}")]
    [ProducesResponseType(typeof(InvoiceDto), StatusCodes.Status200OK)]
    [ProducesResponseType(StatusCodes.Status404NotFound)]
    public async Task<ActionResult<InvoiceDto>> GetInvoice(Guid id)
    {
        _logger.LogInformation("Getting invoice with ID: {InvoiceId}", id);
        
        var query = new GetInvoiceByIdQuery(id);
        var invoice = await _mediator.Send(query);
        
        if (invoice == null)
        {
            return NotFound();
        }
        
        return Ok(invoice);
    }

    /// <summary>
    /// Subscribe to invoice events via Dapr
    /// </summary>
    [HttpPost("events/invoice-created")]
    [Topic("pubsub-servicebus", "InvoiceCreatedEvent")]
    public async Task<IActionResult> HandleInvoiceCreated([FromBody] InvoiceCreatedEventDto eventDto)
    {
        _logger.LogInformation("Received InvoiceCreated event for invoice {InvoiceId}", eventDto.InvoiceId);
        
        // Handle the event (e.g., trigger Fortnox sync, send notifications, etc.)
        
        return Ok();
    }

    /// <summary>
    /// Subscribe to Kivra events via Dapr
    /// </summary>
    [HttpPost("events/invoice-sent-to-kivra")]
    [Topic("pubsub-servicebus", "InvoiceSentToKivraEvent")]
    public async Task<IActionResult> HandleInvoiceSentToKivra([FromBody] InvoiceSentToKivraEventDto eventDto)
    {
        _logger.LogInformation("Received InvoiceSentToKivra event for invoice {InvoiceId}", eventDto.InvoiceId);
        
        // Handle the event (e.g., update status, send confirmation, etc.)
        
        return Ok();
    }
}

/// <summary>
/// Request model for creating an invoice
/// </summary>
public record CreateInvoiceRequest(
    Guid CustomerId,
    string InvoiceNumber,
    decimal TotalAmount,
    string Currency,
    DateOnly InvoiceDate,
    DateOnly DueDate,
    List<InvoiceLineDto> Lines);

/// <summary>
/// Event DTO for invoice created events
/// </summary>
public record InvoiceCreatedEventDto(
    Guid InvoiceId,
    Guid CustomerId,
    string InvoiceNumber,
    decimal Amount);

/// <summary>
/// Event DTO for invoice sent to Kivra events
/// </summary>
public record InvoiceSentToKivraEventDto(
    Guid InvoiceId,
    Guid CustomerId,
    string InvoiceNumber);