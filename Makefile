##################
# Variables
##################

DOCKER_COMPOSE = docker compose -f ./.deployment/docker/docker-compose.yml --env-file ./.deployment/docker/.env
DOCKER_EXEC_PHP = docker exec -it queue-practice-cli

##################
# Docker compose
##################

dc_build:
	${DOCKER_COMPOSE} build

dc_start:
	${DOCKER_COMPOSE} start

dc_stop:
	${DOCKER_COMPOSE} stop

dc_up:
	${DOCKER_COMPOSE} up -d

dc_up_build:
	${DOCKER_COMPOSE} up -d --build

dc_ps:
	${DOCKER_COMPOSE} ps

dc_logs:
	${DOCKER_COMPOSE} logs -f

dc_down:
	${DOCKER_COMPOSE} down -v --rmi=all --remove-orphans

dc_restart:
	make dc_stop dc_start

##################
# App
##################

app_bash:
	${DOCKER_EXEC_PHP} bash
com_i:
	${DOCKER_EXEC_PHP} composer install
com_r:
	${DOCKER_EXEC_PHP} composer require
add_order:
	${DOCKER_EXEC_PHP} php public/index.php app:send-orders
send_notifications:
    ${DOCKER_EXEC_PHP} php public/index.php app:send-notifications
read_sms_notifications:
	${DOCKER_EXEC_PHP} php public/index.php app:handler:handle-notification sms
read_email_notifications:
	${DOCKER_EXEC_PHP} php public/index.php app:handler:handle-notification email
send_analytics:
    ${DOCKER_EXEC_PHP} php public/index.php app:send-analytics
handle_normal_analytics:
	${DOCKER_EXEC_PHP} php public/index.php app:handler:handle-analytics normal
handle_high_analytics:
	${DOCKER_EXEC_PHP} php public/index.php app:handler:handle-analytics high