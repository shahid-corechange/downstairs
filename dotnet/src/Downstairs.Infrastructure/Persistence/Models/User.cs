namespace Downstairs.Infrastructure.Persistence.Models;

public partial class User
{
    public long Id { get; set; }

    public string FirstName { get; set; } = null!;

    public string LastName { get; set; } = null!;

    public string? Email { get; set; }

    public DateTime? EmailVerifiedAt { get; set; }

    public string? Cellphone { get; set; }

    public string? DialCode { get; set; }

    public DateTime? CellphoneVerifiedAt { get; set; }

    public string? IdentityNumber { get; set; }

    public DateTime? IdentityNumberVerifiedAt { get; set; }

    public string Password { get; set; } = null!;

    public string? RememberToken { get; set; }

    public DateTime? LastSeen { get; set; }

    public string Status { get; set; } = null!;

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public DateTime? DeletedAt { get; set; }

    public virtual ICollection<CashierAttendance> CashierAttendanceCheckInCausers { get; set; } = new List<CashierAttendance>();

    public virtual ICollection<CashierAttendance> CashierAttendanceCheckOutCausers { get; set; } = new List<CashierAttendance>();

    public virtual ICollection<CashierAttendance> CashierAttendanceUsers { get; set; } = new List<CashierAttendance>();

    public virtual ICollection<Credit> CreditIssuers { get; set; } = new List<Credit>();

    public virtual ICollection<CreditTransaction> CreditTransactionIssuers { get; set; } = new List<CreditTransaction>();

    public virtual ICollection<CreditTransaction> CreditTransactionUsers { get; set; } = new List<CreditTransaction>();

    public virtual ICollection<Credit> CreditUsers { get; set; } = new List<Credit>();

    public virtual ICollection<CustomerDiscount> CustomerDiscounts { get; set; } = new List<CustomerDiscount>();

    public virtual ICollection<CustomerUser> CustomerUsers { get; set; } = new List<CustomerUser>();

    public virtual ICollection<Deviation> Deviations { get; set; } = new List<Deviation>();

    public virtual ICollection<Employee> Employees { get; set; } = new List<Employee>();

    public virtual ICollection<FixedPrice> FixedPrices { get; set; } = new List<FixedPrice>();

    public virtual ICollection<Invoice> Invoices { get; set; } = new List<Invoice>();

    public virtual ICollection<LaundryOrder> LaundryOrderCausers { get; set; } = new List<LaundryOrder>();

    public virtual ICollection<LaundryOrderHistory> LaundryOrderHistories { get; set; } = new List<LaundryOrderHistory>();

    public virtual ICollection<LaundryOrder> LaundryOrderUsers { get; set; } = new List<LaundryOrder>();

    public virtual ICollection<Notification> Notifications { get; set; } = new List<Notification>();

    public virtual ICollection<Order> Orders { get; set; } = new List<Order>();

    public virtual ICollection<PriceAdjustment> PriceAdjustments { get; set; } = new List<PriceAdjustment>();

    public virtual ICollection<PropertyUser> PropertyUsers { get; set; } = new List<PropertyUser>();

    public virtual ICollection<RutCoApplicant> RutCoApplicants { get; set; } = new List<RutCoApplicant>();

    public virtual ICollection<ScheduleChangeRequest> ScheduleChangeRequests { get; set; } = new List<ScheduleChangeRequest>();

    public virtual ICollection<ScheduleCleaningChangeRequest> ScheduleCleaningChangeRequests { get; set; } = new List<ScheduleCleaningChangeRequest>();

    public virtual ICollection<ScheduleEmployee> ScheduleEmployees { get; set; } = new List<ScheduleEmployee>();

    public virtual ICollection<Schedule> Schedules { get; set; } = new List<Schedule>();

    public virtual ICollection<StoreSale> StoreSales { get; set; } = new List<StoreSale>();

    public virtual ICollection<StoreUser> StoreUsers { get; set; } = new List<StoreUser>();

    public virtual ICollection<SubscriptionStaffDetail> SubscriptionStaffDetails { get; set; } = new List<SubscriptionStaffDetail>();

    public virtual ICollection<Subscription> Subscriptions { get; set; } = new List<Subscription>();

    public virtual ICollection<TeamUser> TeamUsers { get; set; } = new List<TeamUser>();

    public virtual ICollection<TimeAdjustment> TimeAdjustments { get; set; } = new List<TimeAdjustment>();

    public virtual ICollection<UnassignSubscription> UnassignSubscriptions { get; set; } = new List<UnassignSubscription>();

    public virtual ICollection<UserSetting> UserSettings { get; set; } = new List<UserSetting>();

    public virtual ICollection<WorkHour> WorkHours { get; set; } = new List<WorkHour>();
}