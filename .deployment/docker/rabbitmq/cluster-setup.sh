#!/bin/bash

echo "Waiting of all RabbitMQ nodes..."
sleep 30

echo "Stopping the application on 2 and 3 nodes..."
docker exec rabbitmq2 rabbitmqctl stop_app
docker exec rabbitmq3 rabbitmqctl stop_app

echo "Reset 2 and 3 nodes..."
docker exec rabbitmq2 rabbitmqctl reset
docker exec rabbitmq3 rabbitmqctl reset

echo "Connection 2 node to the cluster..."
docker exec rabbitmq2 rabbitmqctl join_cluster rabbit@rabbitmq1

echo "Connection 3 node to the cluster..."
docker exec rabbitmq3 rabbitmqctl join_cluster rabbit@rabbitmq1

echo "Running the application on 2 and 3 nodes..."
docker exec rabbitmq2 rabbitmqctl start_app
docker exec rabbitmq3 rabbitmqctl start_app

echo "Checking cluster's status..."
docker exec rabbitmq1 rabbitmqctl cluster_status

echo "Setting up the High Availability policies..."
docker exec rabbitmq1 rabbitmqctl set_policy ha-all ".*" '{"ha-mode":"all","ha-sync-mode":"automatic"}' --apply-to all

echo "Cluster has been set up!"
echo ""
echo "Management UI is available on the nodes:"
echo "  - Узел 1: http://localhost:15672"
echo "  - Узел 2: http://localhost:15673"
echo "  - Узел 3: http://localhost:15674"