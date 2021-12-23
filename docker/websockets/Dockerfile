FROM node:16-alpine

WORKDIR /var/www

RUN npm i -g laravel-echo-server --no-audit

EXPOSE 6001

CMD ["laravel-echo-server", "start", "--force"]
