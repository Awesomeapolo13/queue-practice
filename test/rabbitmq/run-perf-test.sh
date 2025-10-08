#!/bin/bash

# Цвета для вывода
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${BLUE}=== RabbitMQ PerfTest ===${NC}\n"

# Загружаем переменные окружения из правильного пути
ENV_FILE="$(dirname "$0")/../../.deployment/docker/.env"
if [ -f "$ENV_FILE" ]; then
    source "$ENV_FILE"
else
    echo "Ошибка: не найден файл .env по пути: $ENV_FILE"
    exit 1
fi

# Путь к docker-compose
COMPOSE_FILE="$(dirname "$0")/../../.deployment/docker/docker-compose.yml"
DOCKER_COMPOSE="docker compose -f $COMPOSE_FILE --env-file $ENV_FILE"

# Функция для запуска теста
run_test() {
    local queue_type=$1
    local queue_name=$2
    local extra_args=$3

    echo -e "${GREEN}Тестирование: ${queue_type}${NC}"
    echo "Очередь: ${queue_name}"
    echo -e "${YELLOW}Параметры: producers=${PERF_TEST_PRODUCERS}, consumers=${PERF_TEST_CONSUMERS}, rate=${PERF_TEST_RATE} msg/s, size=${PERF_TEST_SIZE} bytes, prefetch=${PERF_TEST_PREFETCH}${NC}\n"

    ${DOCKER_COMPOSE} --profile testing run --rm perf-test \
        --uri "amqp://${RABBIT_USER}:${RABBIT_PASSWORD}@${RABBIT_HOST}:${RABBIT_PORT}" \
        --queue "${queue_name}" \
        --predeclared \
        --producers "${PERF_TEST_PRODUCERS}" \
        --consumers "${PERF_TEST_CONSUMERS}" \
        --rate "${PERF_TEST_RATE}" \
        --size "${PERF_TEST_SIZE}" \
        --qos "${PERF_TEST_PREFETCH}" \
        --time 60 \
        --auto-delete false \
        ${extra_args}

    echo -e "\n"
}

# Тесты
run_test "Классическая очередь" "email_notifications" ""
run_test "Кворумная очередь" "orders" "--queue-args x-queue-type=quorum"
run_test "Stream" "stream" "--queue-args x-queue-type=stream"

echo -e "${BLUE}=== Тесты завершены ===${NC}"