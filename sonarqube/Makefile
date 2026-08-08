SHELL := /bin/sh
COMPOSE := docker compose --project-directory $(CURDIR) --file $(CURDIR)/compose.yml

.PHONY: config pull up stop down restart status logs scan

config:
	$(COMPOSE) config

pull:
	$(COMPOSE) pull database sonarqube

up:
	$(COMPOSE) up --detach database sonarqube

stop:
	$(COMPOSE) stop

down:
	$(COMPOSE) down

restart:
	$(COMPOSE) restart database sonarqube

status:
	$(COMPOSE) ps

logs:
	$(COMPOSE) logs --follow sonarqube

scan:
	@if ! grep -Eq '^SONAR_TOKEN=.+$$' "$(CURDIR)/.env" 2>/dev/null && [ -z "$$SONAR_TOKEN" ]; then \
		echo "SONAR_TOKEN is missing. Add it to sonarqube/.env or export it before scanning."; \
		exit 1; \
	fi
	$(COMPOSE) --profile scanner run --rm scanner
