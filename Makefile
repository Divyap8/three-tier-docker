# ─────────────────────────────────────────────────────────────
#  Makefile — Three-Tier Docker Compose Shortcuts
# ─────────────────────────────────────────────────────────────
.PHONY: help up down build restart logs ps shell-nginx shell-php shell-mysql health clean

help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | \
	  awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-18s\033[0m %s\n", $$1, $$2}'

up: ## Start all containers (detached)
	docker compose up -d --build

down: ## Stop and remove containers
	docker compose down

build: ## Rebuild images without cache
	docker compose build --no-cache

restart: ## Restart all containers
	docker compose restart

logs: ## Follow logs for all containers
	docker compose logs -f

ps: ## Show running containers and health status
	docker compose ps

shell-nginx: ## Open shell in Nginx container
	docker exec -it nginx_web sh

shell-php: ## Open shell in PHP container
	docker exec -it php_app sh

shell-mysql: ## Open MySQL prompt
	docker exec -it mysql_db mysql -u$${DB_USER:-appuser} -p$${DB_PASSWORD:-secret} $${DB_NAME:-appdb}

health: ## Check health of all containers
	@echo "\n── Container Health ──────────────────────────────────"
	@docker inspect --format '{{.Name}}  →  {{.State.Health.Status}}' \
	  nginx_web php_app mysql_db 2>/dev/null || echo "Some containers not running"

clean: ## Remove containers, volumes, and images
	docker compose down -v --rmi local
	@echo "Clean complete."
