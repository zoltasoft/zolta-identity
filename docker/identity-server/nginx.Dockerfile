FROM nginx:1.27-alpine@sha256:65645c7bb6a0661892a8b03b89d0743208a18dd2f3f17a54ef4b76fb8e2f2a10

COPY docker/identity-server/nginx.conf /etc/nginx/conf.d/default.conf
COPY apps/identity-server/public /var/www/html/public

RUN rm -f /var/www/html/public/storage \
    && ln -s ../storage/app/public /var/www/html/public/storage
