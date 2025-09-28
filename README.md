# NovaDoc Maker

NovaDoc Maker is a simple and efficient tool to quickly generate professional quotes and invoices.

## Technical Stack

### Backend
- **PHP 8.4**: Latest stable version with modern features and performance improvements
- **Symfony 7.3**: Enterprise-grade PHP framework
- **Doctrine ORM**: Powerful object-relational mapper for database interactions

### Infrastructure
- **Docker**: Containerization for consistent development and deployment environments
- **Nginx**: High-performance web server and reverse proxy
- **PHP-FPM**: FastCGI Process Manager for efficient PHP request handling
- **PostgreSQL 17**: Advanced open-source database with robust features for complex data management

## Prerequisites

- Docker and Docker Compose

## Installation

Clone the repository:
```bash
git clone https://github.com/DamienL97r/nova_doc_maker.git
```

Install dependencies:
```bash
composer install
```

Build and start Docker containers:
```bash
cp compose.override.yaml.dist compose.override.yaml
docker compose build --no-cache
docker compose up -d --wait
```

Run server:
```bash
symfony serve
```

Enjoy your link : [http://127.0.0.1:8000](http://127.0.0.1:8000)

## Tailwind

Compile CSS
```bash
php bin/console tailwind:build --watch
```

### Deploying

When you deploy, run the tailwind:build command before the asset-map:compile command so the built file is available:

```bash
php bin/console tailwind:build --minify
php bin/console asset-map:compile
```