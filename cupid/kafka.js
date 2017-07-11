'use strict';
const KafkaProxy = require('kafka-proxy');

const kafkaProxy = new KafkaProxy({
    wsPort: 9999,
    kafka: 'localhost:2181/',
});

kafkaProxy.listen();