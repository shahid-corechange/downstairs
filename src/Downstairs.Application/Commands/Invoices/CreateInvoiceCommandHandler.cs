using Downstairs.Application.Commands.Invoices;
using Downstairs.Application.Common.Interfaces;
using Downstairs.Domain.Entities;
using Downstairs.Domain.ValueObjects;
using MediatR;

namespace Downstairs.Application.Commands.Invoices;

/// <summary>
/// Handler for creating a new invoice
/// </summary>
public class CreateInvoiceCommandHandler(
    IInvoiceRepository invoiceRepository,
    ICustomerRepository customerRepository,
    IUnitOfWork unitOfWork) : IRequestHandler<CreateInvoiceCommand, Guid>
{
    private readonly IInvoiceRepository _invoiceRepository = invoiceRepository;
    private readonly ICustomerRepository _customerRepository = customerRepository;
    private readonly IUnitOfWork _unitOfWork = unitOfWork;

    public async Task<Guid> Handle(CreateInvoiceCommand request, CancellationToken cancellationToken)
    {
        // Verify customer exists
        var customer = await _customerRepository.GetByIdAsync(request.CustomerId, cancellationToken);
        if (customer is null)
        {
            throw new InvalidOperationException($"Customer with ID {request.CustomerId} not found");
        }

        // Check if invoice number already exists
        var existingInvoice = await _invoiceRepository.GetByInvoiceNumberAsync(
            request.InvoiceNumber, cancellationToken);
        if (existingInvoice is not null)
        {
            throw new InvalidOperationException($"Invoice with number {request.InvoiceNumber} already exists");
        }

        // Create invoice lines
        var lines = request.Lines.Select(line => new InvoiceLine(
            line.Description,
            line.Quantity,
            new Money(line.UnitPrice, line.Currency),
            0.25m)); // Default VAT rate

        // Create total amount
        var totalAmount = new Money(request.TotalAmount, request.Currency);

        // Create invoice entity
        var invoice = Invoice.Create(
            request.InvoiceNumber,
            request.CustomerId,
            totalAmount,
            request.InvoiceDate,
            request.DueDate,
            lines);

        // Save to repository
        await _invoiceRepository.AddAsync(invoice, cancellationToken);
        await _unitOfWork.SaveChangesAsync(cancellationToken);

        return invoice.Id;
    }
}