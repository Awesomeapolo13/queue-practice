FROM rabbitmq:4.1.3-management-alpine

# Включаем необходимые плагины
RUN rabbitmq-plugins enable rabbitmq_stream rabbitmq_mqtt

# Копируем конфигурационный файл если нужен
# COPY rabbitmq.conf /etc/rabbitmq/rabbitmq.conf

# Экспонируем портов:
# 5552 - stream protocol
# 1883 - MQTT
# 8883 - MQTT over SSL (опционально)
EXPOSE 5552 1883 8883