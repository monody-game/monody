FROM node:alpine

WORKDIR /usr/src/app

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ADD . .

RUN yarn install

ENTRYPOINT ["/entrypoint.sh"]

CMD ["npm", "run", "dev"]
