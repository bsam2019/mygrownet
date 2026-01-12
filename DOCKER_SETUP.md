# Docker Setup for MyGrowNet

## Prerequisites

1. Install Docker Desktop for Windows from: https://www.docker.com/products/docker-desktop/
2. Restart your computer after installation
3. Ensure Docker Desktop is running (whale icon in system tray)

## First Time Setup

### Step 1: Copy Environment File

```bash
# Copy the Docker environment file
copy .env.docker .env
```

### Step 2: Build and Start Containers

```bash
# Build and start all containers
docker-compose up -d

# This will:
# - Download required images (first time only, ~5-10 minutes)
# - Build PHP container with all extensions
# - Start MySQL, Redis, Nginx, Node
# - Install Composer dependencies
# - Install npm dependencies
```

### Step 3: Install Dependencies

```bash
# Install PHP dependencies
docker-compose exec app composer install

# Generate application key (if not set)
docker-compose exec app php artisan key:generate

# Run migrations
docker-compose exec app php artisan migrate

# Seed database (optional)
docker-compose exec app php artisan db:seed
```

### Step 4: Access Your Application

- **Application**: http://localhost:8000
- **Vite Dev Server**: http://localhost:5173
- **MySQL**: localhost:3306 (use TablePlus, MySQL Workbench, etc.)

## Daily Usage

### Start Containers

```bash
# Start all services
docker-compose up -d

# Or start specific services
docker-compose up -d app nginx mysql
```

### Stop Containers

```bash
# Stop all services
docker-compose down

# Stop and remove volumes (deletes database!)
docker-compose down -v
```

### View Logs

```bash
# View all logs
docker-compose logs

# View specific service logs
docker-compose logs app
docker-compose logs nginx
docker-compose logs mysql

# Follow logs in real-time
docker-compose logs -f app
```

### Run Artisan Commands

```bash
# Run any artisan command
docker-compose exec app php artisan migrate
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan queue:work
docker-compose exec app php artisan tinker
```

### Run Composer Commands

```bash
# Install package
docker-compose exec app composer require package/name

# Update dependencies
docker-compose exec app composer update
```

### Run NPM Commands

```bash
# Install npm package
docker-compose exec node npm install package-name

# Build assets
docker-compose exec node npm run build
```

### Access Container Shell

```bash
# Access PHP container
docker-compose exec app bash

# Access MySQL
docker-compose exec mysql mysql -u root -psecret mygrownet

# Access Redis
docker-compose exec redis redis-cli
```

## Troubleshooting

### Port Already in Use

If port 8000 is already in use, edit `docker-compose.yml`:

```yaml
nginx:
  ports:
    - "8001:80"  # Change 8000 to 8001 or any available port
```

### Permission Issues

```bash
# Fix storage permissions
docker-compose exec app chmod -R 775 storage bootstrap/cache
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
```

### Database Connection Issues

Make sure `.env` has:
```
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=mygrownet
DB_USERNAME=root
DB_PASSWORD=secret
```

### Clear Everything and Start Fresh

```bash
# Stop and remove everything
docker-compose down -v

# Remove all images
docker-compose down --rmi all

# Start fresh
docker-compose up -d --build
```

## Useful Commands

```bash
# See running containers
docker-compose ps

# Restart a service
docker-compose restart app

# Rebuild containers
docker-compose up -d --build

# View container resource usage
docker stats

# Remove unused Docker resources
docker system prune -a
```

## Multiple Projects

To run multiple Laravel projects simultaneously:

1. Change ports in each project's `docker-compose.yml`:
   ```yaml
   # Project 1: MyGrowNet
   nginx:
     ports:
       - "8000:80"
   
   # Project 2: Other Project
   nginx:
     ports:
       - "8001:80"
   ```

2. Change container names to avoid conflicts:
   ```yaml
   # Project 1
   container_name: mygrownet_app
   
   # Project 2
   container_name: otherproject_app
   ```

3. Use different network names:
   ```yaml
   # Project 1
   networks:
     - mygrownet
   
   # Project 2
   networks:
     - otherproject
   ```

## Tips

- Keep Docker Desktop running in the background
- Use `docker-compose up -d` to run containers in detached mode
- Use `docker-compose logs -f` to watch logs in real-time
- Containers automatically restart unless stopped manually
- Database data persists in Docker volumes even after stopping containers

## Need Help?

- Docker Documentation: https://docs.docker.com/
- Laravel Docker: https://laravel.com/docs/sail (similar concept)
- Check logs: `docker-compose logs`
