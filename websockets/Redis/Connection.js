const { createClient } = require('redis');

const client = createClient({ url: 'redis://redis:6379' });
client.on('error', (err) => console.log('Redis Client', err));
client.connect()

exports.client = client
