
# Migration
cd .\dotnet\src\Downstairs.Infrastructure\
cd .\src\Downstairs.Infrastructure\
dotnet ef migrations add InitialCreate --output-dir Persistence/Migrations      

# Run Project
dotnet run --project .\dotnet\Downstairs.AppHost  
dotnet run --project Downstairs.AppHost  

cd ..


