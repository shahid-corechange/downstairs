# 🏢 Downstairs Solution

A modern cloud-native **Hednowega ChangeCore** business platform built with .NET 10, featuring real-time monitoring, automated business processes, and enterprise-grade orchestration.

## 🌟 **Key Features**

- 📊 **Real-time Business Dashboard** - Live metrics, logs, and health monitoring
- 🔄 **Automated Business Workflows** - Customer creation, invoicing, and document delivery
- 🏗️ **Clean Architecture** - Domain → Application → Infrastructure → Presentation
- 🚀 **Microservices** - Event-driven with .NET Aspire orchestration
- 🔐 **Enterprise Security** - Azure integration with role-based access
- 📈 **Business Intelligence** - Performance analytics and alerting

## 🏗️ Architecture Overview

### **Core Principles**
- **Event-Driven**: Dapr pub/sub with Azure Service Bus
- **CQRS**: MediatR for commands and queries  
- **Microservices**: .NET Aspire orchestration with Docker containers
- **Caching**: Redis for distributed caching and state management
- **Resilience**: Polly for retry and circuit breaker patterns
- **Monitoring**: Real-time LogPortal with business metrics and alerts

### **Business Domains**
- **Customer Management** - Fortnox integration for customer lifecycle
- **Invoice Processing** - Automated generation and Kivra delivery
- **Document Workflow** - End-to-end business document handling
- **Performance Analytics** - Real-time business intelligence and reporting

## 🚀 Quick Start

### Prerequisites
- **.NET 10.0 SDK** (Latest preview)
- **Docker Desktop** (for container orchestration)
- **Azure Service Bus** namespace (for production messaging)
- **Visual Studio 2022 17.11+** or **VS Code** (recommended)

### 🏃‍♂️ **One-Command Startup**

```powershell
# Start infrastructure containers
.\manage-containers.ps1 start

# In another terminal - Start Aspire application stack
dotnet run --project Downstairs.AppHost
```

### 📁 **Manual Setup**

#### 1. Clone Repository
```bash
git clone https://github.com/shahid-corechange/downstairs.git
cd downstairs
```

### 2. Configure Local Environment (`.env`)

The root `.env` file now ships with sample connection strings only. Update the values to match your environment before running the stack:

```
ConnectionStrings__downstairsdb=Server=localhost;Port=3306;Database=downstairs;Uid=your-user;Pwd=your-password;CharSet=utf8mb4;
ConnectionStrings__ServiceBus=Endpoint=sb://your-namespace.servicebus.windows.net/;SharedAccessKeyName=RootManageSharedAccessKey;SharedAccessKey=your-shared-access-key
```

### 3. Configure Azure Service Bus

#### Option A: User Secrets (Recommended for Development)
```bash
cd Downstairs.AppHost
dotnet user-secrets set "ConnectionStrings:ServiceBus" "Endpoint=sb://your-namespace.servicebus.windows.net/;SharedAccessKeyName=RootManageSharedAccessKey;SharedAccessKey=your-key"
```

#### Option B: Environment Variables
```bash
# PowerShell
$env:ConnectionStrings__ServiceBus = "Endpoint=sb://your-namespace.servicebus.windows.net/;SharedAccessKeyName=RootManageSharedAccessKey;SharedAccessKey=your-key"

# Bash
export ConnectionStrings__ServiceBus="Endpoint=sb://your-namespace.servicebus.windows.net/;SharedAccessKeyName=RootManageSharedAccessKey;SharedAccessKey=your-key"
```

#### Option C: appsettings.Development.json
```json
{
  "ConnectionStrings": {
    "ServiceBus": "Endpoint=sb://your-namespace.servicebus.windows.net/;SharedAccessKeyName=RootManageSharedAccessKey;SharedAccessKey=your-key"
  }
}
```

### 4. Run the Application Stack
```bash
# Start infrastructure (MySQL + Redis)
.\manage-containers.ps1 start

# Start Aspire application services  
dotnet run --project Downstairs.AppHost
```

### 🌐 **Access Points**
- **Aspire Dashboard**: `https://localhost:17161` - Service orchestration
- **LogPortal**: `http://localhost:5005` - **Real-time business monitoring** 📊
- **Admin Panel**: `http://localhost:5004` - Administrative interface
- **API Gateway**: `http://localhost:5000` - Main API entry point
- **API Documentation**: `http://localhost:5001/swagger` - OpenAPI specs

## 🔧 Configuration

### Azure Service Bus Setup

#### Current Environment: Staging
- **Namespace**: `v2-stage-downstairs.servicebus.windows.net`
- **Policy**: `RootManageSharedAccessKey`

#### Getting Your Connection String
1. Go to [Azure Portal](https://portal.azure.com)
2. Navigate to your Service Bus namespace: `v2-stage-downstairs`
3. Go to **Settings** → **Shared access policies**
4. Click on **RootManageSharedAccessKey**
5. Copy the **Primary Connection String**

#### Connection String Format
```
Endpoint=sb://v2-stage-downstairs.servicebus.windows.net/;SharedAccessKeyName=RootManageSharedAccessKey;SharedAccessKey=YOUR_KEY_HERE
```

### Environment Variables Reference

Values can be supplied via standard environment variables or the root `.env` file. Replace the sample placeholders in `.env` with real credentials before running locally.

| Variable | Description | Example |
|----------|-------------|---------|
| `ConnectionStrings__ServiceBus` | Azure Service Bus connection string | `Endpoint=sb://...` |
| `ConnectionStrings__downstairs` | Database connection (auto-generated by Aspire) | `Server=localhost;Database=...` |
| `ConnectionStrings__redis` | Redis connection (auto-generated by Aspire) | `localhost:6379` |
| `ASPNETCORE_ENVIRONMENT` | Application environment | `Development` or `Production` |

### Dapr Configuration

The solution includes Dapr components for messaging and state management:

- **Service Bus Pub/Sub**: `dapr/components/servicebus-pubsub.yaml`
- **Redis Pub/Sub**: `dapr/components/redis-pubsub.yaml` (development fallback)
- **Redis State Store**: `dapr/components/redis-statestore.yaml`

## 🏗️ Solution Architecture

### **📁 Project Structure**
```
Downstairs.sln
├── 🎯 Downstairs.AppHost/              # .NET Aspire orchestrator
├── 🔧 Downstairs.ServiceDefaults/      # Shared configuration & policies
├── src/
│   ├── 🌐 Downstairs.ApiGateway/       # YARP API Gateway + Dapr
│   ├── 🔌 Downstairs.Api/              # Core REST API service
│   ├── 📋 Downstairs.Application/      # CQRS handlers (MediatR)
│   ├── 🏛️ Downstairs.Domain/           # Domain entities + events
│   ├── 🗄️ Downstairs.Infrastructure/   # EF Core, Dapr, Polly
│   ├── ⏰ Downstairs.Jobs/             # Quartz.NET background jobs
│   ├── 🎛️ Downstairs.Blazor.Admin/    # Administrative dashboard
│   ├── 📊 Downstairs.LogPortal/        # **Real-time monitoring portal**
│   ├── 🔗 Downstairs.Integrations.Fortnox/ # Customer & invoice management
│   └── 📄 Downstairs.Integrations.Kivra/   # Document delivery service
└── tests/
    ├── 🧪 Downstairs.UnitTests/        # Unit testing suite
    └── 🔬 Downstairs.IntegrationTests/ # End-to-end testing
```

### **🐳 Container Organization (`downstairs-local` network)**

#### **Infrastructure Services**
- **`downstairs-mysql`** - MySQL 8.0 database (Port 3306)
- **`downstairs-redis`** - Redis cache & pub/sub (Port 6379)

#### **Application Services**  
- **`downstairs-api-gateway`** - Entry point & routing (Port 5000)
- **`downstairs-api`** - Core business API (Port 5001)
- **`downstairs-jobs`** - Background processing (Port 5003)
- **`downstairs-admin`** - Management interface (Port 5004)
- **`downstairs-logportal`** - **Business monitoring** (Port 5005) 📊

## 🔄 Event-Driven Architecture

### Domain Events
- `CustomerCreated`: Published when new customer added
- `InvoiceCreated`: Published when new invoice generated  
- `InvoiceSentToKivra`: Published after Kivra delivery

### Pub/Sub Configuration
- **Development**: Uses Redis pub/sub (`pubsub` component)
- **Production**: Uses Azure Service Bus (`pubsub-servicebus` component)

## 📊 **LogPortal - Real-Time Business Monitoring**

### **🎯 Key Features**
- **Live Business Metrics** - Customer creation, invoice processing, job success rates
- **Service Health Monitoring** - Real-time status of all microservices  
- **Performance Analytics** - API response times, cache hit rates, error tracking
- **Smart Alerting** - Email notifications for business-critical events
- **Log Stream** - Live log aggregation with filtering and search
- **30-Second Refresh** - Auto-updating dashboard with SignalR

### **📈 Business Intelligence Dashboard**
```
┌─ Customers Created Today: 15    ┌─ Service Health Status ────┐
├─ Invoices Created Today: 23     │ ✅ API Gateway: Healthy    │
├─ Total Active Customers: 1,247  │ ✅ API Service: Healthy    │
└─ Avg Invoice Amount: €2,456     │ ⚠️  Jobs: Degraded        │
                                  │ ✅ Redis: Healthy          │
┌─ Job Execution (24h) ──────────┬─ API Performance ──────────┤
│ ✅ Success: 142 (94.7%)        │ Gateway: 45ms avg          │
│ ❌ Failed: 8 (5.3%)            │ API: 120ms avg             │
│ ⏱️  Avg Time: 2.3s             │ Success Rate: 99.2%        │
└─────────────────────────────────└─────────────────────────────┘
```

## ⏰ Automated Business Processes (Quartz.NET)

| Schedule | Job | Business Purpose |
|----------|-----|------------------|
| **Every 1 min** | `CreateFortnoxCustomerJob` | Demo customer lifecycle automation |
| **Every 1 min** | `CreateFortnoxInvoiceJob` | Demo invoice generation process |
| **Every 1 min** | `SendInvoicesToKivraJob` | Demo document delivery workflow |

*Note: Jobs run every minute for demonstration. In production, use appropriate business schedules.*

## 🛠️ Development Guide

### **Development Stack**
- **.NET 10.0** - Latest framework with C# 14 features
- **Aspire 9.5.1** - Container orchestration and service discovery  
- **Docker** - Containerized infrastructure services
- **MySQL 8.0** - Primary database with EF Core migrations
- **Redis 7** - Distributed caching and pub/sub messaging

### **🔧 Development Commands**

#### **Container Management**
```powershell
# Infrastructure lifecycle
.\manage-containers.ps1 start    # Start MySQL + Redis
.\manage-containers.ps1 status   # Check container health  
.\manage-containers.ps1 logs     # View container logs
.\manage-containers.ps1 stop     # Stop all containers
.\manage-containers.ps1 clean    # Remove all resources
```

#### **Application Development**
```bash
# Build entire solution
dotnet build

# Run unit tests  
dotnet test

# Start development with hot reload
dotnet run --project Downstairs.AppHost

# Entity Framework operations
dotnet ef database update --project src/Downstairs.Infrastructure
dotnet ef migrations add NewMigration --project src/Downstairs.Infrastructure
```

### **🔍 Debugging & Monitoring**

#### **Real-Time Monitoring**
- **LogPortal**: `http://localhost:5005` - Business metrics and logs
- **Aspire Dashboard**: `https://localhost:17161` - Service health
- **Container Logs**: `.\manage-containers.ps1 logs [service-name]`

#### **Database Management**  
```bash
# Connect to MySQL (password: downstairs123)
mysql -h localhost -P 3306 -u root -p downstairs

# Redis CLI access
redis-cli -h localhost -p 6379
```

## 🐳 **Container Architecture**

### **Network Topology**: `downstairs-local` (172.20.0.0/16)
```
┌─ Infrastructure Containers ────────────────────┐
│ downstairs-mysql:3306   (Database)            │  
│ downstairs-redis:6379   (Cache + Pub/Sub)     │
└────────────────────────────────────────────────┘
           ↕️  (Docker Network: downstairs-local)
┌─ Application Containers (.NET Aspire) ────────┐
│ downstairs-api-gateway:5000  (Entry Point)    │
│ downstairs-api:5001          (Core API)       │  
│ downstairs-jobs:5003         (Background)     │
│ downstairs-admin:5004        (Management)     │
│ downstairs-logportal:5005    (Monitoring)     │
└────────────────────────────────────────────────┘
```

### **Service Dependencies**
- **API** → MySQL + Redis + ServiceBus
- **Jobs** → MySQL + Redis + ServiceBus + API  
- **Admin** → API
- **LogPortal** → API + Jobs (for monitoring)
- **API Gateway** → All services (routing)

## 🔒 Security & Compliance

### **Development Environment**
- 🔐 **Secrets Management**: User Secrets for sensitive configuration
- 🌐 **SSL Bypass**: Development certificate validation disabled  
- 🔄 **Local Messaging**: Redis pub/sub for development workflow
- 🏠 **Network Isolation**: `downstairs-local` Docker network

### **Production Environment**
- 🔐 **Azure Key Vault**: Enterprise secret management
- 🛡️ **SSL/TLS**: Full certificate validation and HTTPS enforcement
- 🚌 **Azure Service Bus**: Enterprise messaging with SLA guarantees  
- 📊 **Azure Monitor**: Comprehensive logging and alerting
- 🔍 **Application Insights**: Performance monitoring and analytics

### **Access Control**
- **Role-Based Security**: Admin and user role separation
- **API Authentication**: JWT token-based authentication (ready for implementation)
- **Network Segmentation**: Container-level network isolation
- **Audit Logging**: All business operations tracked in LogPortal

## 📊 **Monitoring & Observability**

### **Real-Time Monitoring Stack**
- 📈 **LogPortal Dashboard**: Business-focused metrics and KPIs
- 🎛️ **Aspire Dashboard**: Service health and container orchestration
- 📊 **OpenTelemetry**: Distributed tracing across microservices
- 📝 **Serilog**: Structured logging with correlation IDs
- ✅ **Health Checks**: Automated service monitoring with alerts
- 🔔 **Smart Alerts**: Email notifications for business-critical events

### **Business Intelligence Features**
- 📈 **Live Metrics**: Customer growth, invoice processing, job success rates
- 🎯 **Performance Tracking**: API response times, cache efficiency, error rates  
- 🚨 **Proactive Alerting**: Threshold-based notifications for business events
- 📊 **Historical Analytics**: Trend analysis and business insights
- 🔍 **Log Aggregation**: Centralized logging with advanced filtering

## 🚀 **Deployment & Production**

### **Technology Stack**
- **Framework**: .NET 10.0 with C# 14 features
- **Orchestration**: .NET Aspire with Docker containers
- **Database**: MySQL 8.0 with Entity Framework Core migrations
- **Caching**: Redis 7.x for distributed caching and session state
- **Messaging**: Azure Service Bus for reliable pub/sub messaging
- **Monitoring**: Custom LogPortal + OpenTelemetry + Azure Monitor

### **Scalability & Performance**
- **Microservices Architecture**: Independent scaling and deployment
- **Event-Driven Design**: Asynchronous processing with Dapr
- **Caching Strategy**: Multi-level caching with Redis
- **Circuit Breakers**: Polly for resilience and fault tolerance
- **Container Orchestration**: Production-ready with Kubernetes support

## 🆕 **Recent Updates & Improvements**

### **✅ .NET 10 Upgrade (October 2025)**
- 🚀 **Framework Modernization**: Upgraded entire solution from .NET 9 to .NET 10
- 🔧 **C# 14 Features**: Enhanced primary constructors and improved collection expressions  
- 📦 **Package Updates**: Latest Aspire 9.5.1, Entity Framework, and third-party libraries
- ⚡ **Performance**: Native AOT compilation support and improved startup times

### **✅ LogPortal Implementation**
- 📊 **Real-Time Dashboard**: Live business metrics with 30-second auto-refresh
- 🎯 **Business Focus**: Customer creation, invoice processing, job success tracking
- 🔔 **Smart Alerting**: Email notifications for business-critical events
- 📈 **Performance Monitoring**: API response times, cache efficiency, error tracking
- 🔍 **Log Aggregation**: Centralized logging with advanced filtering and correlation

### **✅ Container Organization**  
- 🐳 **Docker Network**: All containers grouped under `downstairs-local` network
- 🏷️ **Consistent Naming**: Professional container naming convention (`downstairs-*`)
- 🛠️ **Management Tools**: PowerShell script for complete lifecycle management
- 🔗 **Service Discovery**: Improved networking and container communication
- 📋 **Documentation**: Comprehensive container architecture documentation

### **🔄 Ongoing Improvements**
- [ ] **Aspire 10.x**: Upgrade to latest Aspire packages for .NET 10
- [ ] **Package Modernization**: Update all third-party dependencies
- [ ] **C# 14 Adoption**: Leverage new language features throughout codebase  
- [ ] **Blazor Enhancements**: Adopt .NET 10 rendering and interactivity APIs
- [ ] **Production Deployment**: Azure Container Apps integration

## 🎯 **Business Value Delivered**

### **For Operations Teams**
- 📊 **Real-Time Visibility**: Live monitoring of all business processes
- 🚨 **Proactive Alerting**: Early warning system for business disruptions
- 🔍 **Centralized Logging**: Single pane of glass for troubleshooting
- 📈 **Performance Insights**: Data-driven optimization opportunities

### **For Development Teams** 
- 🚀 **Modern Stack**: Latest .NET 10 with cutting-edge features
- 🐳 **Professional DevEx**: Container orchestration with one-command startup
- 🔧 **Comprehensive Tooling**: Management scripts and monitoring dashboards
- 🏗️ **Scalable Architecture**: Microservices ready for enterprise deployment

### **For Business Stakeholders**
- 💼 **Process Automation**: Customer and invoice lifecycle automation
- 📊 **Business Intelligence**: Live KPIs and performance analytics  
- ⚡ **Operational Efficiency**: Streamlined workflows and reduced manual processes
- 🎯 **Growth Enablement**: Scalable platform ready for business expansion

## 🤝 Contributing

### **Development Workflow**
1. **Fork** the repository and create feature branch
2. **Setup** development environment with container management tools
3. **Develop** using .NET 10 best practices and clean architecture
4. **Test** with comprehensive unit and integration test suites
5. **Monitor** changes using LogPortal dashboard
6. **Submit** pull request with detailed description

### **Code Standards**
- ✅ **Clean Architecture** - Domain-driven design principles
- ✅ **SOLID Principles** - Maintainable and testable code
- ✅ **.NET 10 Features** - Modern C# 14 language constructs
- ✅ **Event-Driven** - Asynchronous messaging patterns
- ✅ **Observability** - Comprehensive logging and monitoring

## 📝 License

This project is licensed under the **MIT License** - see the [LICENSE](LICENSE) file for details.

---

**🏢 Built by Hednowega ChangeCore** | **🚀 Powered by .NET 10 & Azure** | **📊 Monitored by LogPortal**