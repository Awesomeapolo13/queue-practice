FROM rabbitmq:3.13-management-alpine

RUN rabbitmq-plugins enable rabbitmq_stream rabbitmq_mqtt rabbitmq_web_mqtt

COPY ./.deployment/docker/rabbitmq/rabbitmq.conf /etc/rabbitmq/rabbitmq.conf
COPY ./.deployment/docker/rabbitmq/definitions.json /etc/rabbitmq/definitions.json

# Ports export:
# 5552 - stream protocol
# 1883 - MQTT
# 15675 - MQTT over WebSockets
EXPOSE 5552 1883 15675