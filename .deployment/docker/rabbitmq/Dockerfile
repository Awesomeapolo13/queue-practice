FROM rabbitmq:4.1.3-management-alpine

# Включаем необходимые плагины
RUN rabbitmq-plugins enable rabbitmq_stream

# Копируем конфигурационный файл если нужен
# COPY rabbitmq.conf /etc/rabbitmq/rabbitmq.conf

# Экспонируем порт для stream protocol
EXPOSE 5552