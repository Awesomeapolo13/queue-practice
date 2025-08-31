# Queue practicing

## Stack

- PHP-8.4

## Deploy

1) Copy `.deployment/docker/.env.dist` as `.env` file
2) Repeat first step but with `.env` in the project root
3) Run commands below
```shell
make dc_up_build
```

```shell
make com_i
```

## Description

Before start, you need to initiate a topology by this command:

```shell
make top_init
```

This command will create exchanges, queues and their bindings, according to `config/rabbitmq/topology.php` file. Now you can run commands to publish messages:

1) To use a direct exchange:
```shell
make add_orders
```

2) To use a topic exchange:
```shell
make send_notifications
```

3) To use a headers exchange:
```shell
make log_analitics
```

4) To use a fanout exchange
```shell
make send_system_event
```

Consumers 

1) Поднять в контейнерах несколько очередей
2) Создать несколько паблишеров с разным типом обменника
3) 
