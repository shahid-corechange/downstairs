using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading;
using System.Threading.Tasks;
using Downstairs.Application.Common.Interfaces;
using Microsoft.EntityFrameworkCore;
using DomainInvoice = Downstairs.Domain.Entities.Invoice;
using PersistenceInvoice = Downstairs.Infrastructure.Persistence.Models.Invoice;

namespace Downstairs.Infrastructure.Persistence.Repositories;

/// <summary>
/// Repository implementation for domain invoices backed by the scaffolded persistence layer.
/// </summary>
internal sealed class InvoiceRepository(DownstairsDbContext context) : RepositoryBase<PersistenceInvoice>(context), IInvoiceRepository
{
	public async Task<DomainInvoice?> GetByIdAsync(long id, CancellationToken cancellationToken = default)
	{
		var entity = await QueryWithCustomer().FirstOrDefaultAsync(invoice => invoice.Id == id, cancellationToken);
		return entity is null ? null : MapToDomain(entity);
	}

	public async Task<IReadOnlyCollection<DomainInvoice>> GetByCustomerIdAsync(long customerId, CancellationToken cancellationToken = default)
	{
		var entities = await QueryWithCustomer()
			.Where(invoice => invoice.CustomerId == customerId)
			.ToListAsync(cancellationToken);

		return entities.Select(MapToDomain).ToArray();
	}

	public async Task<IReadOnlyCollection<DomainInvoice>> GetAllAsync(CancellationToken cancellationToken = default)
	{
		var entities = await QueryWithCustomer().ToListAsync(cancellationToken);
		return entities.Select(MapToDomain).ToArray();
	}

	public async Task<DomainInvoice?> GetByInvoiceNumberAsync(string invoiceNumber, CancellationToken cancellationToken = default)
	{
		if (string.IsNullOrWhiteSpace(invoiceNumber))
		{
			throw new ArgumentException("Invoice number must be provided.", nameof(invoiceNumber));
		}

		var entity = await QueryWithCustomer()
			.FirstOrDefaultAsync(invoice => invoice.Remark == invoiceNumber || invoice.Id.ToString() == invoiceNumber, cancellationToken);

		return entity is null ? null : MapToDomain(entity);
	}

	public Task AddAsync(DomainInvoice invoice, CancellationToken cancellationToken = default)
	{
		throw new NotSupportedException("Persisting domain invoices with the scaffolded persistence model is not implemented yet.");
	}

	private IQueryable<PersistenceInvoice> QueryWithCustomer()
	{
		return Query()
			.Include(invoice => invoice.Customer);
	}

	private static DomainInvoice MapToDomain(PersistenceInvoice entity)
	{
		return DomainInvoice.FromPersistence(
			entity.Id,
			ResolveInvoiceNumber(entity),
			entity.CustomerId,
			entity.TotalNet,
			entity.TotalGross,
			entity.TotalVat,
			entity.TotalRut,
			entity.Status,
			ToDateTimeOffset(entity.CreatedAt),
			ToNullableDateTimeOffset(entity.UpdatedAt),
			ToNullableDateTimeOffset(entity.SentAt),
			ToNullableDateTimeOffset(entity.DueAt),
			ToNullableDateTimeOffset(entity.DeletedAt),
			entity.UserId,
			entity.FortnoxInvoiceId,
			entity.FortnoxTaxReductionId,
			entity.Type,
			entity.Month,
			entity.Year,
			entity.Remark,
			entity.FortnoxInvoiceId?.ToString());
	}

	private static string ResolveInvoiceNumber(PersistenceInvoice entity)
	{
		if (!string.IsNullOrWhiteSpace(entity.Remark))
		{
			return entity.Remark;
		}

		return entity.Id.ToString();
	}

	private static DateTimeOffset ToDateTimeOffset(DateTime? value)
	{
		var dateTime = value ?? DateTime.UtcNow;
		return new DateTimeOffset(DateTime.SpecifyKind(dateTime, DateTimeKind.Utc));
	}

	private static DateTimeOffset? ToNullableDateTimeOffset(DateTime? value)
	{
		return value is null ? null : new DateTimeOffset(DateTime.SpecifyKind(value.Value, DateTimeKind.Utc));
	}
}

