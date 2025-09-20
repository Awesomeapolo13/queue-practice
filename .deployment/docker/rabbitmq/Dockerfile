FROM rabbitmq:4.1.3-management-alpine

# Включаем необходимые плагины
RUN rabbitmq-plugins enable rabbitmq_stream rabbitmq_mqtt rabbitmq_web_mqtt

# Копируем конфигурационный файл если нужен
# COPY rabbitmq.conf /etc/rabbitmq/rabbitmq.conf

# Экспонируем портов:
# 5552 - stream protocol
# 1883 - MQTT
# 15675 - MQTT over WebSockets
EXPOSE 5552 1883 15675