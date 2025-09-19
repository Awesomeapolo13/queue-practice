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

To read messages from them:
```shell
make read_sms_notifications
make read_email_notifications
```

3) To use a headers exchange:
```shell
make send_analytics
```
To read messages from them:
```shell
make handle_normal_analytics
make handle_high_analytics
```

4) To use a fanout exchange
```shell
make send_audit
```

To read messages from them:
```shell
make handle_audit
make handle_monitoring
make handle_backup
```

5) To use reply to functional run the command below. This command sends messages to `rpc_queue` and consume messages from `rpc_reply_to_queue` for one minute.
```shell
make send_repl_to
```

Then run a consumer command below to consume messages and publish replies to `rpc_reply_to_queue`.
```shell
make handle_repl_to
```
