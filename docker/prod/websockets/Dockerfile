FROM node:alpine

WORKDIR /var/www

RUN npm i -g pm2
RUN yarn

CMD ["pm2-runtime", "websockets/index.js"]

EXPOSE 6001
