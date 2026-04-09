# 🐙 OctoNotify: GitHub Release Subscriber API

A professional monolithic PHP 8.4 service for tracking new releases in GitHub repositories and providing instant email notifications to subscribers.

## 🏗 Architecture: Hexagonal (Ports & Adapters)

The project is built following the **Hexagonal Architecture** principles, ensuring full independence of business logic from external tools and infrastructure:

- **Domain/Core**: Contains interfaces (**Ports**) and core business rules. Independent of specific libraries.
- **Application**: Contains **Use Cases** (e.g., `SubscriptionService`) that orchestrate domain logic.
- **Infrastructure**: Contains **Adapters** — concrete implementations for Database (PDO), HTTP (Guzzle), Cache (Redis), and Metrics (Prometheus).

## 🚀 Key Features & Tech Stack

- **PHP 8.4**: Leverages modern language features (Attributes, Readonly, Intersection Types).
- **PHP-DI**: Automated dependency injection with **Autowiring**.
- **Glassmorphism UI**: Modern, sleek frontend built with vanilla JS/CSS.
- **Swagger/OpenAPI 3.0**: Interactive API documentation and automatic schema generation.
- **Prometheus Metrics**: Real-time monitoring of system state and API activity.
- **Redis Caching**: Optimized GitHub API interactions and resilience against Rate Limits.

## 🛠 Setup and Installation

The project is fully containerized using Docker.

1. **Start the environment**:
   ```bash
   docker-compose up --build -d
   ```
2. **Web Interface**: Available at `http://localhost:8080/`.
3. **API Documentation**: Swagger UI available at `http://localhost:8080/api/docs`.
4. **Metrics**: Available at `http://localhost:8080/metrics`.
5. **MailHog (Email Testing)**: View outgoing emails at `http://localhost:8025/`.

## 🧪 Quality Control & Testing

This project adheres to high coding standards (PSR-12, SOLID):

- **Run Tests (PHPUnit)**:
  ```bash
  docker exec subscriber-api-app-1 composer test
  ```
- **Static Analysis (PHPStan)**:
  ```bash
  docker exec subscriber-api-app-1 composer analyze
  ```
- **Coding Style Check (PHPCS)**:
  ```bash
  docker exec subscriber-api-app-1 composer lint
  ```

## 🔒 Security

All restricted API endpoints are protected using the `App-Api-Key` header. Security configuration is managed via `.env`.

## 📈 Background Scanner (Daemon)

A background worker (`scripts/scanner.php`) periodically checks GitHub for new repository tags and orchestrates notifications. The system is designed to be resilient to network failures and GitHub API Rate Limits (429 handling).
