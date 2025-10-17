# Downstairs Container Organization

This document outlines the container organization and networking strategy for the Downstairs solution.

## Network Architecture

### Network: `downstairs-local` (172.20.0.0/16)

All Downstairs services are organized under a single Docker network for:
- **Service Discovery**: Containers can communicate using service names
- **Security**: Network isolation from other Docker containers
- **Monitoring**: Centralized traffic monitoring and logging
- **Development**: Consistent local development environment

## Container Groups

### 🏗️ Infrastructure Services
- **`downstairs-mysql`** - MySQL 8.0 database server
- **`downstairs-redis`** - Redis cache and session store

### 🚀 Application Services (Managed by Aspire)
- **`downstairs-api-gateway`** - YARP reverse proxy and API gateway
- **`downstairs-api`** - Main REST API service
- **`downstairs-jobs`** - Quartz.NET background job processor
- **`downstairs-admin`** - Blazor admin dashboard
- **`downstairs-logportal`** - Real-time monitoring and logging dashboard

## Service Dependencies

```
API Gateway (Port: 5000)
├── API (Port: 5001)
│   ├── MySQL (Port: 3306)
│   ├── Redis (Port: 6379)
│   └── Azure Service Bus (External)
├── Admin (Port: 5004)
│   └── API
└── LogPortal (Port: 5005)
    ├── API
    └── Jobs

Jobs (Port: 5003)
├── MySQL (Port: 3306)
├── Redis (Port: 6379)
├── Azure Service Bus (External)
└── API
```

## Development Commands

### Start Infrastructure Only
```bash
# Start MySQL and Redis containers
docker-compose up -d

# Verify network creation
docker network ls | grep downstairs-local
```

### Start Full Application Stack
```bash
# Start Aspire orchestrator (includes all services)
dotnet run --project Downstairs.AppHost

# This will:
# 1. Connect to existing downstairs-local network
# 2. Start all .NET services with proper naming
# 3. Configure service dependencies
```

### Monitor Container Status
```bash
# List all Downstairs containers
docker ps --filter "name=downstairs-*"

# View container logs
docker logs downstairs-mysql
docker logs downstairs-redis

# Inspect network
docker network inspect downstairs-local
```

## Environment Configuration

### Service URLs
- **API Gateway**: http://localhost:5000
- **API**: http://localhost:5001
- **Jobs**: http://localhost:5003
- **Admin**: http://localhost:5004
- **LogPortal**: http://localhost:5005

### Database Connections
- **MySQL**: `Server=localhost;Port=3306;Database=downstairs;Uid=root;Pwd=password`
- **Redis**: `localhost:6379`

### Health Check Endpoints
- All services expose: `{service-url}/health`
- Aspire Dashboard: Available through Aspire host startup logs

## Production Considerations

For production deployment, consider:
1. **External Networks**: Use existing production networks
2. **Service Mesh**: Integration with Istio, Linkerd, or similar
3. **Container Registry**: Push images to Azure Container Registry
4. **Orchestration**: Deploy via Azure Container Apps or Kubernetes
5. **Monitoring**: Integrate with Azure Monitor, Application Insights
6. **Security**: Implement proper network policies and service authentication