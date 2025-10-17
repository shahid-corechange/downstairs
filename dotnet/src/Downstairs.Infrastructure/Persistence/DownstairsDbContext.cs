using Downstairs.Infrastructure.Persistence.Models;
using Downstairs.ServiceDefaults.Configuration;
using Microsoft.EntityFrameworkCore;
using TaskEntity = Downstairs.Infrastructure.Persistence.Models.Task;

namespace Downstairs.Infrastructure.Persistence;

public partial class DownstairsDbContext : DbContext
{
    public DownstairsDbContext()
    {
    }

    public DownstairsDbContext(DbContextOptions<DownstairsDbContext> options)
        : base(options)
    {
    }

    public virtual DbSet<ActivityLog> ActivityLogs { get; set; }

    public virtual DbSet<Address> Addresses { get; set; }

    public virtual DbSet<AuthenticationLog> AuthenticationLogs { get; set; }

    public virtual DbSet<BlindIndex> BlindIndexes { get; set; }

    public virtual DbSet<BlockDay> BlockDays { get; set; }

    public virtual DbSet<City> Cities { get; set; }

    public virtual DbSet<Country> Countries { get; set; }

    public virtual DbSet<Credit> Credits { get; set; }

    public virtual DbSet<CreditCreditTransaction> CreditCreditTransactions { get; set; }

    public virtual DbSet<CreditTransaction> CreditTransactions { get; set; }

    public virtual DbSet<Customer> Customers { get; set; }

    public virtual DbSet<CustomerDiscount> CustomerDiscounts { get; set; }

    public virtual DbSet<CustomerUser> CustomerUsers { get; set; }

    public virtual DbSet<CustomTask> CustomTasks { get; set; }

    public virtual DbSet<Deviation> Deviations { get; set; }

    public virtual DbSet<Employee> Employees { get; set; }

    public virtual DbSet<FailedJob> FailedJobs { get; set; }

    public virtual DbSet<Feedback> Feedbacks { get; set; }

    public virtual DbSet<FixedPrice> FixedPrices { get; set; }

    public virtual DbSet<FixedPriceRow> FixedPriceRows { get; set; }

    public virtual DbSet<GlobalSetting> GlobalSettings { get; set; }

    public virtual DbSet<Invoice> Invoices { get; set; }

    public virtual DbSet<KeyPlace> KeyPlaces { get; set; }

    public virtual DbSet<LeaveRegistration> LeaveRegistrations { get; set; }

    public virtual DbSet<LeaveRegistrationDetail> LeaveRegistrationDetails { get; set; }

    public virtual DbSet<Metum> Meta { get; set; }

    public virtual DbSet<Migration> Migrations { get; set; }

    public virtual DbSet<ModelHasPermission> ModelHasPermissions { get; set; }

    public virtual DbSet<ModelHasRole> ModelHasRoles { get; set; }

    public virtual DbSet<MonthlyWorkHour> MonthlyWorkHours { get; set; }

    public virtual DbSet<Notification> Notifications { get; set; }

    public virtual DbSet<OauthRemoteToken> OauthRemoteTokens { get; set; }

    public virtual DbSet<OldCustomer> OldCustomers { get; set; }

    public virtual DbSet<OldOrder> OldOrders { get; set; }

    public virtual DbSet<Order> Orders { get; set; }

    public virtual DbSet<OrderFixedPrice> OrderFixedPrices { get; set; }

    public virtual DbSet<OrderFixedPriceRow> OrderFixedPriceRows { get; set; }

    public virtual DbSet<OrderRow> OrderRows { get; set; }

    public virtual DbSet<PasswordResetToken> PasswordResetTokens { get; set; }

    public virtual DbSet<Permission> Permissions { get; set; }

    public virtual DbSet<PersonalAccessToken> PersonalAccessTokens { get; set; }

    public virtual DbSet<PriceAdjustment> PriceAdjustments { get; set; }

    public virtual DbSet<PriceAdjustmentRow> PriceAdjustmentRows { get; set; }

    public virtual DbSet<Product> Products { get; set; }

    public virtual DbSet<ProductCategory> ProductCategories { get; set; }

    public virtual DbSet<Property> Properties { get; set; }

    public virtual DbSet<PropertyType> PropertyTypes { get; set; }

    public virtual DbSet<PropertyUser> PropertyUsers { get; set; }

    public virtual DbSet<Role> Roles { get; set; }

    public virtual DbSet<RutCoApplicant> RutCoApplicants { get; set; }

    public virtual DbSet<ScheduleCleaning> ScheduleCleanings { get; set; }

    public virtual DbSet<ScheduleCleaningChangeRequest> ScheduleCleaningChangeRequests { get; set; }

    public virtual DbSet<ScheduleCleaningDeviation> ScheduleCleaningDeviations { get; set; }

    public virtual DbSet<ScheduleCleaningProduct> ScheduleCleaningProducts { get; set; }

    public virtual DbSet<ScheduleCleaningTask> ScheduleCleaningTasks { get; set; }

    public virtual DbSet<ScheduleEmployee> ScheduleEmployees { get; set; }

    public virtual DbSet<ScheduleStore> ScheduleStores { get; set; }

    public virtual DbSet<ScheduleStoreDetail> ScheduleStoreDetails { get; set; }

    public virtual DbSet<Service> Services { get; set; }

    public virtual DbSet<ServiceQuarter> ServiceQuarters { get; set; }

    public virtual DbSet<Subscription> Subscriptions { get; set; }

    public virtual DbSet<SubscriptionDetail> SubscriptionDetails { get; set; }

    public virtual DbSet<SubscriptionProduct> SubscriptionProducts { get; set; }

    public virtual DbSet<SubscriptionStaffDetail> SubscriptionStaffDetails { get; set; }

    public virtual DbSet<TaskEntity> Tasks { get; set; }

    public virtual DbSet<Team> Teams { get; set; }

    public virtual DbSet<TeamUser> TeamUsers { get; set; }

    public virtual DbSet<TimeAdjustment> TimeAdjustments { get; set; }

    public virtual DbSet<Translation> Translations { get; set; }

    public virtual DbSet<UnassignSubscription> UnassignSubscriptions { get; set; }

    public virtual DbSet<User> Users { get; set; }

    public virtual DbSet<UserDiscount> UserDiscounts { get; set; }

    public virtual DbSet<UserInfo> UserInfos { get; set; }

    public virtual DbSet<UserOtp> UserOtps { get; set; }

    public virtual DbSet<UserSetting> UserSettings { get; set; }

    public virtual DbSet<WorkHour> WorkHours { get; set; }

    protected override void OnConfiguring(DbContextOptionsBuilder optionsBuilder)
    {
        if (optionsBuilder.IsConfigured)
        {
            return;
        }

        var connectionString = ConnectionStringHelper.Resolve("downstairsdb");
        if (string.IsNullOrWhiteSpace(connectionString))
        {
            return;
        }

        optionsBuilder.UseMySql(connectionString, ServerVersion.AutoDetect(connectionString));
    }

    protected override void OnModelCreating(ModelBuilder modelBuilder)
    {
        modelBuilder
            .UseCollation("utf8mb4_unicode_ci")
            .HasCharSet("utf8mb4");

        modelBuilder.ApplyConfigurationsFromAssembly(typeof(DownstairsDbContext).Assembly);

        OnModelCreatingPartial(modelBuilder);
    }

    partial void OnModelCreatingPartial(ModelBuilder modelBuilder);
}
