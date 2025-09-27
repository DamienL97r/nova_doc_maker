# NovaDoc Maker


## Technical Stack

### Backend
- **PHP 8.4**: Latest stable version with modern features and performance improvements
- **Symfony 7.3**: Enterprise-grade PHP framework for robust API development
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
git clone https://github.com/damienL97r/nova_doc_maker
```


Build and start Docker containers:
```bash
cp compose.override.yaml.dist compose.override.yaml
docker compose build --no-cache
docker compose up -d --wait
```