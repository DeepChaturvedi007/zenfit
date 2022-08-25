#!/bin/bash

CLUSTER='zf-cluster'
FAMILY='zf-task'
TASK='zf-task'
SERVICE='zf-cluster-service'
VERSION=${CIRCLE_BUILD_NUM}
APP_SERVER_NAME='app.zenfitapp.com'
APP_ENV='prod'
git config --global user.email $DOCKER_EMAIL
git config --global user.name $DOCKER_USER

make_task_volumes() {
  task_vol_template='[
    {
      "host": {
        "sourcePath": "/var/run/docker.sock"
      },
      "name": "docker_sock"
    },
    {
      "host": {
        "sourcePath": "/proc/"
      },
      "name": "proc"
    },
    {
      "host": {
        "sourcePath": "/sys/fs/cgroup/"
      },
      "name": "cgroup"
    },
    {
      "host": {
        "sourcePath": "/opt/datadog-agent/run"
      },
      "name": "pointdir"
    },
    {
      "host": {
        "sourcePath": "/etc/passwd"
      },
      "name": "passwd"
    }
  ]'
  echo "$task_vol"
  task_vol=$(printf "$task_vol_template")
}

make_task_def() {
  task_template='[
    {
      "name": "nginx",
      "image": "%s.dkr.ecr.eu-central-1.amazonaws.com/zenfit-nginx:prod",
      "essential": true,
      "cpu": 10,
      "memoryReservation": 1000,
      "portMappings": [
        {
          "containerPort": 80,
          "hostPort": 80
        }
      ],
      "environment" : [
        { "name" : "APP_SERVER_NAME", "value" : "%s" }
      ],
      "links": [
        "app"
      ],
      "volumesFrom": [
        { "sourceContainer": "app" }
      ]
    },
    {
      "name": "app",
      "image": "%s.dkr.ecr.eu-central-1.amazonaws.com/zenfit:%s",
      "essential": true,
      "cpu": 10,
      "memoryReservation": 1000,
      "portMappings": [
        {
          "containerPort": 9000,
          "hostPort": 9000
        }
      ],
      "links": [
        "datadog-agent"
      ],
      "environment": [
        { "name": "DD_AGENT_HOST", "value": "datadog-agent" },
        { "name": "DD_TRACE_ANALYTICS_ENABLED", "value": "true" }
      ]
    },
    {
      "name": "datadog-agent",
      "image": "datadog/agent:latest",
      "cpu": 10,
      "memory": 256,
      "essential": true,
      "portMappings": [
        {
          "containerPort": 8126,
          "hostPort": 8126
        }
      ],
      "mountPoints": [
        {
          "containerPath": "/var/run/docker.sock",
          "sourceVolume": "docker_sock",
          "readOnly": true
        },
        {
          "containerPath": "/host/sys/fs/cgroup",
          "sourceVolume": "cgroup",
          "readOnly": true
        },
        {
          "containerPath": "/opt/datadog-agent/run",
          "sourceVolume": "pointdir",
          "readOnly": false
        },
        {
          "containerPath": "/host/proc",
          "sourceVolume": "proc",
          "readOnly": true
        },
        {
          "containerPath": "/etc/passwd",
          "sourceVolume": "passwd",
          "readOnly": true
        }
      ],
      "environment": [
        { "name": "DD_API_KEY", "value": "%s" },
        { "name": "DD_SITE", "value": "datadoghq.eu" },
        { "name": "DD_APM_ENABLED", "value": "true" },
        { "name": "DD_APM_NON_LOCAL_TRAFFIC", "value": "true" },
        { "name": "DD_PROCESS_AGENT_ENABLED", "value": "true" },
        { "name": "DD_TRACE_DEBUG", "value": "true" },
        { "name": "DD_LOGS_ENABLED", "value": "true" },
        { "name": "DD_LOGS_CONFIG_CONTAINER_COLLECT_ALL", "value": "true" }
      ]
    }
  ]'
  echo "$task_def"
	task_def=$(printf "$task_template" $AWS_ACCOUNT_ID $APP_SERVER_NAME $AWS_ACCOUNT_ID $VERSION $DD_API_KEY)
}

build_docker_image() {
  docker build \
    --build-arg ZF_DATABASE_HOST=$ZF_DATABASE_HOST \
    --build-arg ZF_DATABASE_PORT=$ZF_DATABASE_PORT \
    --build-arg ZF_DATABASE_NAME=$ZF_DATABASE_NAME \
    --build-arg ZF_DATABASE_USER=$ZF_DATABASE_USER \
    --build-arg ZF_DATABASE_PASSWORD=$ZF_DATABASE_PASSWORD \
    --build-arg ZF_STRIPE_PUBLISHABLE_KEY=$ZF_STRIPE_PUBLISHABLE_KEY \
    --build-arg ZF_STRIPE_SECRET_KEY=$ZF_STRIPE_SECRET_KEY \
    --build-arg ZF_APP_HOSTNAME=$ZF_APP_HOSTNAME \
    --build-arg ZF_STRIPE_CLIENT_ID=$ZF_STRIPE_CLIENT_ID \
    --build-arg ASSETS_VERSION=$CIRCLE_BUILD_NUM \
    --build-arg MAILER_USER=$MAILER_USER \
    --build-arg MAILER_PASSWORD=$MAILER_PASSWORD \
    --build-arg JWT_SECRET=$JWT_SECRET \
    --build-arg SQS_PDF_URL=$SQS_PDF_URL \
    --build-arg SQS_VIDEO_URL=$SQS_VIDEO_URL \
    --build-arg SQS_MEDIA_COMPRESSED_URL=$SQS_MEDIA_COMPRESSED_URL \
    --build-arg SQS_VOICE_URL=$SQS_VOICE_URL \
    --build-arg SQS_CHAT_MULTIPLE_URL=$SQS_CHAT_MULTIPLE_URL \
    --build-arg AWS_MEDIA_CONVERT_ROLE=$AWS_MEDIA_CONVERT_ROLE \
    --build-arg MEMCACHED_URL=$MEMCACHED_URL \
    --build-arg APP_ENV=$APP_ENV \
    --build-arg MYFITNESSPAL_CLIENT_ID=$MYFITNESSPAL_CLIENT_ID \
    --build-arg MYFITNESSPAL_CLIENT_SECRET=$MYFITNESSPAL_CLIENT_SECRET \
    --build-arg AWS_ACCESS_KEY_ID=$AWS_ACCESS_KEY_ID \
    --build-arg AWS_SECRET_ACCESS_KEY=$AWS_SECRET_ACCESS_KEY \
    --build-arg SENTRY_DSN=$SENTRY_DSN \
    --build-arg STRIPE_DK_TAX_RATE_ID=$STRIPE_DK_TAX_RATE_ID \
    --build-arg STRIPE_NO_TAX_RATE_ID=$STRIPE_NO_TAX_RATE_ID \
    -t $ECR_URI:$VERSION .
}

build_and_push_docker_image() {
  aws configure set region $AWS_REGION
  $(aws ecr get-login)

  if build_docker_image; then
    docker push $ECR_URI:$VERSION
    #tag and push to git
    git tag -a "$VERSION" -m "version $VERSION"
    return 0
  else
    return 1
  fi
}


register_and_deploy_definition() {
  # Register task definition & volumes
  make_task_def
  make_task_volumes
  json=$(aws ecs register-task-definition --container-definitions "$task_def" --volumes "$task_vol" --family "$FAMILY")
  # Grab revision # using regular bash and grep
  revision=$(echo "$json" | grep -o '"revision": [0-9]*' | grep -Eo '[0-9]+')
  # Deploy revision
  aws ecs update-service --cluster "$CLUSTER" --service "$SERVICE" --task-definition "$TASK":"$revision"
  return 0
}

if build_and_push_docker_image; then
  register_and_deploy_definition
else
  return 1
fi
