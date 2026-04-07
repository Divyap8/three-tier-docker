# 🐳 Three-Tier Architecture with Docker Compose


A production-ready containerized three-tier web application using **Docker Compose** with private network segmentation, health checks, and automatic restart policies.

---

## 📐 Architecture

```
Browser / Client
       │
       ▼  port 80 / 443 (public)
┌─────────────────────┐
│   TIER 1: Nginx     │  ← frontend_net (public-facing)
│   Web Server        │
└────────┬────────────┘
         │  FastCGI  (backend_net — private)
         ▼
┌─────────────────────┐
│   TIER 2: PHP-FPM   │  ← backend_net + db_net
│   App Server        │
└────────┬────────────┘
         │  TCP 3306  (db_net — private, not exposed to host)
         ▼
┌─────────────────────┐
│   TIER 3: MySQL     │  ← db_net only (isolated)
│   Database          │
└─────────────────────┘
```

### Network Segmentation

| Network        | Members            | Purpose                              |
|----------------|--------------------|--------------------------------------|
| `frontend_net` | Nginx              | Public-facing traffic only           |
| `backend_net`  | Nginx ↔ PHP-FPM    | App-tier communication (private)     |
| `db_net`       | PHP-FPM ↔ MySQL    | DB tier isolated — no host exposure  |

---

## 📁 Project Structure

```
three-tier-docker/
├── docker-compose.yml          # Orchestration: all three tiers
├── .env.example                # Environment variable template
├── Makefile                    # Convenience commands
│
├── nginx/
│   ├── nginx.conf              # Main Nginx config
│   └── conf.d/
│       └── default.conf        # Virtual host + FastCGI proxy
│
├── php/
│   ├── Dockerfile              # PHP-FPM 8.2 Alpine image
│   ├── php.ini                 # PHP runtime settings
│   ├── php-fpm.conf            # FPM pool config
│   └── src/
│       ├── bootstrap.php       # DB singleton + router + helpers
│       ├── public/
│       │   └── index.php       # Application entry point
│       └── views/
│           └── home.php        # HTML view
│
├── mysql/
│   ├── my.cnf                  # MySQL server config
│   └── init/
│       └── 01_schema.sql       # Schema + seed data (auto-runs)
│
├── scripts/
│   └── deploy.sh               # One-command deploy script
│
└── .github/
    └── workflows/
        └── ci.yml              # GitHub Actions CI pipeline
```

---

## 🚀 Quick Start

### Prerequisites

- [Docker Desktop](https://docs.docker.com/get-docker/) ≥ 24 (includes Compose v2)
- `make` (optional, for shortcut commands)

### 1. Clone & configure

```bash
git clone https://github.com/your-username/three-tier-docker.git
cd three-tier-docker

cp .env.example .env
# Edit .env and set strong passwords
```

### 2. Deploy (one command)

```bash
bash scripts/deploy.sh
```

Or manually:

```bash
docker compose up -d --build
```

### 3. Verify

| Endpoint              | Description            |
|-----------------------|------------------------|
| `http://localhost`    | Web application        |
| `http://localhost/health` | JSON health status |
| `http://localhost/users`  | Users API (JSON)   |

---

## 🛠 Makefile Commands

```bash
make up          # Build and start all containers
make down        # Stop and remove containers
make restart     # Restart all services
make logs        # Follow all container logs
make ps          # Show container status + health
make health      # Quick health summary
make shell-php   # Open shell in PHP container
make shell-mysql # Open MySQL CLI
make clean       # Remove containers, volumes, images
```

---

## 🏥 Health Checks & Restart Policies

Every container has a **Docker health check** and **`restart: unless-stopped`** policy ensuring automatic recovery with zero manual intervention.

### Nginx
```yaml
healthcheck:
  test: ["CMD", "wget", "--quiet", "--tries=1", "--spider", "http://localhost/health"]
  interval: 30s
  timeout:  10s
  retries:  3
  start_period: 15s
```

### PHP-FPM
```yaml
healthcheck:
  test: ["CMD-SHELL", "php-fpm -t 2>&1 | grep -q 'test is successful'"]
  interval: 30s
  timeout:  10s
  retries:  3
  start_period: 20s
```

### MySQL
```yaml
healthcheck:
  test: ["CMD", "mysqladmin", "ping", "-h", "localhost", "-u", "root", "-p${MYSQL_ROOT_PASSWORD}"]
  interval: 30s
  timeout:  10s
  retries:  5
  start_period: 30s
```

### Dependency chain
```
MySQL (healthy) → PHP-FPM (healthy) → Nginx starts
```
`depends_on` with `condition: service_healthy` ensures Nginx only starts after PHP is ready, and PHP only starts after MySQL is accepting connections.

---

## 🔒 Security Highlights

- **MySQL not exposed to host** — port 3306 is only reachable via the private `db_net` network
- **PHP runs as non-root** (`appuser:appgroup`, UID 1000)
- **`server_tokens off`** in Nginx — hides version from response headers
- **`expose_php = Off`** — hides PHP version from HTTP headers
- **`local_infile = 0`** in MySQL — prevents LOAD DATA LOCAL INFILE attacks
- **`disable_functions`** in PHP blocks shell execution functions
- Hardened security headers: `X-Frame-Options`, `X-Content-Type-Options`, `X-XSS-Protection`

---

## ⚙️ Configuration Reference

### Environment Variables (`.env`)

| Variable              | Default     | Description                    |
|-----------------------|-------------|--------------------------------|
| `APP_ENV`             | `production`| App environment                |
| `DB_NAME`             | `appdb`     | Database name                  |
| `DB_USER`             | `appuser`   | DB application user            |
| `DB_PASSWORD`         | *(required)*| DB user password               |
| `MYSQL_ROOT_PASSWORD` | *(required)*| MySQL root password            |

### Ports

| Service | Internal Port | Host Port | Notes                    |
|---------|---------------|-----------|--------------------------|
| Nginx   | 80            | 80        | HTTP (public)            |
| Nginx   | 443           | 443       | HTTPS (public)           |
| PHP-FPM | 9000          | —         | Private (backend_net)    |
| MySQL   | 3306          | —         | Private (db_net only)    |

---

## 🧩 Key Design Decisions

### Why three separate Docker networks?

Least-privilege network segmentation: the database is completely unreachable from the internet even if Nginx is compromised. An attacker breaching the web tier cannot directly query MySQL.

### Why `depends_on` with `condition: service_healthy`?

Prevents race conditions where PHP tries to connect to MySQL before it's ready to accept connections, or Nginx tries to proxy to PHP before FPM has started.

### Why Alpine base images?

Smaller attack surface, faster pulls, less disk usage. `nginx:1.25-alpine` is ~23MB vs ~140MB for the Debian variant.

---

## 📊 Performance Comparison

| Metric              | Before Docker | After Docker Compose |
|---------------------|---------------|----------------------|
| Environment setup   | ~2 hours      | **~5 minutes**       |
| Config drift risk   | High          | **None** (IaC)       |
| Onboarding new devs | Manual guide  | `bash deploy.sh`     |
| Failure recovery    | Manual        | **Automatic**        |
| Environment parity  | Often broken  | **Guaranteed**       |

---



