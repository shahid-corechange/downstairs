# Downstairs Portal & API

[![Build Status](https://dev.azure.com/downstairs-service/application/_apis/build/status%2FLaravel-App%20-%20CI?repoName=laravel-app&branchName=main)](https://dev.azure.com/downstairs-service/application/_build/latest?definitionId=3&repoName=laravel-app&branchName=main)
[![Nginx](https://img.shields.io/badge/Nginx-00C300?style=flat&logo=nginx&logoColor=white)](https://nginx.com)
[![PHP-FPM](https://img.shields.io/badge/PHP--FPM-777BB4?style=flat&logo=php&logoColor=white)](https://www.php.net)
[![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=flat&logo=laravel&logoColor=white)](https://laravel.com)

This is a repository for the Downstairs Portal & API. It is a Laravel application that uses the [Metronic Theme](https://preview.keenthemes.com/metronic8/demo1/index.html) and [Laravel Sail](https://laravel.com/docs/10.x/sail) for local development.

It is deployed to Azure App Service using [Azure Pipelines](https://azure.microsoft.com/en-us/services/devops/pipelines/). It uses [Nginx](https://nginx.com) and [PHP-FPM](https://www.php.net) to serve the application. It uses [Azure Storage](https://azure.microsoft.com/en-us/services/storage/) for file storage and [Azure Database for MySQL](https://azure.microsoft.com/en-us/services/mysql/) for the database.

## Getting Started

Here are the instructions to get the application running locally. For better development experience, we recommend using [Visual Studio Code](https://code.visualstudio.com/download) with [Visual Studio Code Remote Containers](https://marketplace.visualstudio.com/items?itemName=ms-vscode-remote.remote-containers) to be able to run the application in a dev container that we have already configured.

### Prerequisites

Here are the things you need to install to be able to run the application locally:

- [Docker](https://www.docker.com/get-started)
- [Visual Studio Code](https://code.visualstudio.com/download)
- [Visual Studio Code Remote Containers](https://marketplace.visualstudio.com/items?itemName=ms-vscode-remote.remote-containers)

### Installation

Here are the steps to get the application running locally:

1. Clone the repository

   ```bash
   git clone https://downstairs-service@dev.azure.com/downstairs-service/application/_git/laravel-app
   ```

2. Open the repository in Visual Studio Code, and click the "Reopen in Container" button in the bottom right corner of the window. This will build the dev container and open the repository in the dev container. You can also open the command palette and run the "Remote-Containers: Reopen in Container" command.

3. Open a new terminal in Visual Studio Code and run the following commands:

   ```bash
   yarn dev # Run this if you want to access the portal
   sail up -d
   ```

   Or you can also open the command palette and run the "Tasks: Run Task" command, and select the "Start App" task.

4. Migrate and seed the database

   ```bash
   sail artisan migrate:fresh --seed
   sail artisan ide-helper:models -N # Run this to update intellesense for models
   ```

   Or you can also open the command palette and run the "Tasks: Run Task" command, and select the "Migrate and Seed Database" task.

5. Stop the application

   ```bash
   sail down
   ```

   Or you can also open the command palette and run the "Tasks: Run Task" command, and select the "Stop App" task.

### Running Tests

To run the tests, make sure the application is running and database is migrated, and then run the following command:

```bash
sail test
```

Or you can also open the command palette and run the "Tasks: Run Task" command, and select the "Run Tests" task.

## Tasks

Here are the tasks that you can run from the command palette:

- **Start App**

  Running the application in a container using Laravel Sail. This will also start the database and Redis. This task also build the assets for the portal.

- **Restart App**

  Restarting the application in a container using Laravel Sail. This will also restart the database and Redis.

- **Stop App**

  Stopping the application in a container using Laravel Sail. This will also stop the database and Redis.

- **Migrate Database**

  Create all the tables in the database.

- **Migrate and Seed Database**

  Create all the tables in the database and seed the database with fake data.

- **Run Tests**

  Run all the unit and integration tests. Before the tests are run, this task will clear the caches first.

- **Clear Caches**

  Clear all the caches such as config, route, view, and application cache.

## Contributing

Please read the [Contributing Wiki](https://dev.azure.com/downstairs-service/application/_wiki/wikis/application.wiki/3/Contributing) for details on commit message guidelines, and the process for submitting pull requests.

## References

- [46elks](https://www.46elks.com/docs/overview)
- [Azure Blob Storage](https://docs.microsoft.com/en-us/azure/storage/blobs/)
- [Azure Notification Hubs](https://docs.microsoft.com/en-us/azure/notification-hubs/)
- [Bootstrap 5](https://getbootstrap.com/docs/5.2/getting-started/introduction/)
- [Carbon Dates](https://carbon.nesbot.com/docs/)
- [Form Validation JS](https://formvalidation.io/)
- [Fortnox](https://www.fortnox.se/developer)
- [Laravel](https://laravel.com/docs/10.x)
- [Laravel Data](https://spatie.be/docs/laravel-data/v3/introduction)
- [Laravel Permission](https://spatie.be/docs/laravel-permission/v5/basic-usage/basic-usage)
- [Laravel Faker](https://fakerphp.github.io/)
- [Laravel Authentication Log](https://rappasoft.com/docs/laravel-authentication-log/v1/introduction)
- [Laravel Datatables](https://yajrabox.com/docs/laravel-datatables/10.0/introduction)
- [Metronic Theme Demo1](https://preview.keenthemes.com/metronic8/demo1/index.html)
- [Multiplex Meta](https://github.com/kolossal-io/laravel-multiplex)
