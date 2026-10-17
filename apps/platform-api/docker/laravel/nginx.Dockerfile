FROM nginx:1.27-alpine
COPY docker/laravel/nginx.conf.template /etc/nginx/templates/default.conf.template
COPY public /var/www/html/public

RUN rm -f /var/www/html/public/storage \
    && ln -s ../storage/app/public /var/www/html/public/storage
