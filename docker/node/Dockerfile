FROM node:16-alpine

RUN mkdir -m 777 /.npm
RUN mkdir -m 777 /.yarn
RUN touch /.yarnrc && chmod 777 /.yarnrc

WORKDIR /usr/src/app

RUN npm i -g nodemon
RUN yarn
