using System;
using System.Collections.Generic;

namespace Downstairs.Infrastructure.Persistence.Models;

public partial class LeaveRegistrationDetail
{
    public long Id { get; set; }

    public long LeaveRegistrationId { get; set; }

    public string? FortnoxAbsenceTransactionId { get; set; }

    public DateTime StartAt { get; set; }

    public DateTime EndAt { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public virtual LeaveRegistration LeaveRegistration { get; set; } = null!;
}
