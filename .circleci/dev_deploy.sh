#!/bin/sh

docker build \
  --build-arg ZF_DATABASE_HOST=$ZF_DATABASE_HOST \
  --build-arg ZF_DATABASE_PORT=$ZF_DATABASE_PORT \
  --build-arg ZF_DATABASE_NAME=$1 \
  --build-arg ZF_DATABASE_USER=$ZF_DATABASE_USER \
  --build-arg ZF_DATABASE_PASSWORD=$ZF_DATABASE_PASSWORD \
  --build-arg ZF_APP_HOSTNAME=$3 \
  --build-arg SQS_PDF_URL=$SQS_PDF_URL \
  --build-arg SQS_VIDEO_URL=$SQS_VIDEO_URL \
  --build-arg SQS_MEDIA_COMPRESSED_URL=$SQS_MEDIA_COMPRESSED_URL \
  --build-arg SQS_VOICE_URL=$SQS_VOICE_URL \
  --build-arg SQS_CHAT_MULTIPLE_URL=$SQS_CHAT_MULTIPLE_URL \
  --build-arg AWS_MEDIA_CONVERT_ROLE=$AWS_MEDIA_CONVERT_ROLE \
  --build-arg MYFITNESSPAL_CLIENT_ID=$MYFITNESSPAL_CLIENT_ID \
  --build-arg MYFITNESSPAL_CLIENT_SECRET=$MYFITNESSPAL_CLIENT_SECRET \
  --build-arg AWS_ACCESS_KEY_ID=$AWS_ACCESS_KEY_ID \
  --build-arg AWS_SECRET_ACCESS_KEY=$AWS_SECRET_ACCESS_KEY \
  --build-arg SENTRY_DSN=$SENTRY_DSN \
  --build-arg STRIPE_DK_TAX_RATE_ID=$STRIPE_DK_TAX_RATE_ID \
  --build-arg STRIPE_NO_TAX_RATE_ID=$STRIPE_NO_TAX_RATE_ID \
  --build-arg ZF_STRIPE_PUBLISHABLE_KEY=$ZF_STRIPE_PUBLISHABLE_KEY \
  --build-arg ZF_STRIPE_SECRET_KEY=$ZF_STRIPE_SECRET_KEY \
  --build-arg APP_ENV=$4 \
  --add-host=api.mixpanel.com:127.0.0.1 \
  --add-host=www.google-analytics.com:127.0.0.1 \
  --add-host=google-analytics.com:127.0.0.1 \
  --add-host=ssl.google-analytics.com:127.0.0.1 \
  -t mathiaslund/zenfit:$2 .

docker login --username=$DOCKER_USER --password=$DOCKER_PASS
docker push mathiaslund/zenfit:$2
