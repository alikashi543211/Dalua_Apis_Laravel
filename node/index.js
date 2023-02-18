const path = require('path');

const http = require('https');
fs = require("fs");

require('dotenv').config({ path: path.join(__dirname, '.env') });


const options = {
    key: fs.readFileSync(process.env.SSL_KEY),
    cert: fs.readFileSync(process.env.SSL_CHAIN)
};

const AWS = require('aws-sdk')
const AWSMqttClient = require('aws-mqtt/lib/NodeClient')
const express = require('express');
const app = express();

const server = http.createServer(options, app);

const io = require("socket.io")(server, {
    cors: {
        origin: "*"
    }
});

app.get('/', (req, res) => {
    res.end('Hello World');
});


server.listen(3000, "0.0.0.0", () => {
    var addr = server.address();
  console.log('   app listening on http://' + addr.address + ':' + addr.port);
});




AWS.config.region = 'us-east-2';
AWS.config.credentials = new AWS.CognitoIdentityCredentials({
    IdentityPoolId: 'us-east-2:14d23770-63c7-4d88-b2ed-c0ba06ae8d94',
});

const { Sequelize, DataTypes } = require('sequelize');

console.log(process.env);

// Option 3: Passing parameters separately (other dialects)
const sequelize = new Sequelize(process.env.DB_NAME, process.env.DB_USER, process.env.DB_PASSWORD, {
  host: process.env.DB_HOST,
  dialect: 'mysql',
  define: {
    timestamps: false
  }
});

process.on('unhandledRejection', error => {
  // Will print "unhandledRejection err is not defined"
  console.log('unhandledRejection', error.message);
});

const CommandLog = sequelize.define('command_log', {
  id: { type: DataTypes.BIGINT, primaryKey: true },
  topic: { type: DataTypes.STRING },
  mac_address: { type: DataTypes.STRING },
  timestamp: { type: DataTypes.STRING },
  command_id: { type: DataTypes.INTEGER },
  response: { type: DataTypes.TEXT },
  user_id: { type: DataTypes.BIGINT },
  device_id: { type: DataTypes.BIGINT, allowNull: true },
  group_id: { type: DataTypes.BIGINT, allowNull: true },
  payload: { type: DataTypes.TEXT },
  status: { type: DataTypes.TINYINT },
  created_at: { type: DataTypes.STRING },
  updated_at: { type: DataTypes.STRING },
});

const Device = sequelize.define('device', {
  id: { type: DataTypes.BIGINT, primaryKey: true },
  uid: { type: DataTypes.STRING },
  topic: { type: DataTypes.STRING },
  device_topic: { type: DataTypes.STRING },
  wifi: { type: DataTypes.STRING },
  product_id: { type: DataTypes.BIGINT },
  aquarium_id: { type: DataTypes.BIGINT },
  group_id: { type: DataTypes.BIGINT },
  user_id: { type: DataTypes.BIGINT },
  name: { type: DataTypes.STRING },
  ip_address: { type: DataTypes.STRING },
  wifi_ssid: { type: DataTypes.STRING },
  timezone: { type: DataTypes.STRING },
  configuration: { type: DataTypes.TEXT },
  completed: { type: DataTypes.TINYINT },
  status: { type: DataTypes.TINYINT },
  mac_address: { type: DataTypes.STRING },
  esp_product_name: { type: DataTypes.STRING },
  created_at: { type: DataTypes.STRING },
  updated_at: { type: DataTypes.STRING },
});
const DeviceHistory = sequelize.define('device_history', {
  id: { type: DataTypes.BIGINT, primaryKey: true },
  mac_address: { type: DataTypes.STRING },
  name: { type: DataTypes.STRING },
  topic: { type: DataTypes.STRING },
  user_id: { type: DataTypes.BIGINT },
  device_id: { type: DataTypes.BIGINT },
  type: { type: DataTypes.INTEGER },
  message: { type: DataTypes.STRING },
  created_at: { type: DataTypes.STRING },
  updated_at: { type: DataTypes.STRING },
});

const client = new AWSMqttClient({
    region: AWS.config.region,
    credentials: AWS.config.credentials,
    endpoint: 'a22m9v9u8tngpf-ats.iot.us-east-2.amazonaws.com', // NOTE: get this value with `aws iot describe-endpoint`
    clientId: 'node-mqtt-client-' + (Math.floor((Math.random() * 100000) + 1)), // clientId to register with MQTT broker. Need to be unique per client
    will: {
      topic: 'WillMsg',
      payload: 'Connection Closed abnormally..!',
      qos: 0,
      retain: false
    }
  });


  client.on('connect', () => {
    client.subscribe('$aws/events/presence/disconnected/#')
    client.subscribe('$aws/events/presence/connected/#')
    client.subscribe('$aws/events/subscriptions/subscribed/#')
    client.subscribe('+/+/+/ack')
})
  client.on('message', (topic, message) => {
    console.log("on message received: ", topic, message);
    if(topic.includes('Dalua-') && !topic.includes('subscribed')){
      var response = JSON.parse(message.toString());
      var macAddress = response.clientId.split("-")[2];
      if(macAddress){
        if(topic.includes('events/presence/disconnected')){
            updateDevice(macAddress, 0);
        }else if(topic.includes('events/presence/connected')){
            updateDevice(macAddress, 1);
        }
      }
    }else if(topic.includes('Dalua-') && topic.includes('subscribed')){
        var response = JSON.parse(message.toString());
        var macAddress = response.clientId.split("-")[2];
        var productName = response.clientId.split("-")[1];
        if(response.topics.length > 0){
            updateDeviceCurrentTopic(macAddress, response.topics[0], productName);
        }
    }else if(topic.includes('/ack')){
        if(message.toString().includes('"commandID":4')){
            var response = JSON.parse(message.toString());
            if(response.fromDevice && response.commandID == 4){
                updateCommandLog(topic, response);
            }
        }

    }
  })
  client.on('close', () => {
    // ...
  })
  client.on('offline', () => {
    // ...
  })

  async function updateCommandLog(topic, response)
  {

    const commandLogRecord = await CommandLog.findOne({ where: { mac_address: response.macAddress }, order: [ ['id', 'DESC'] ] });

    if(commandLogRecord){

        let obj = {
          status: 1,
          response: JSON.stringify(response)
        }
        await commandLogRecord.update(obj).then(num => {});

        sendSocketAck(response);
    }
  }

  async function updateDevice(macAddress, status)
  {
    let obj = {
      status: status
    }
    await Device.update(obj, { where: { mac_address: macAddress } }).then(num => {
      console.log(num);
    });
    var message = status ? "Contected" : "Disconected";
    sendSocketSignal(macAddress, status);
    addLogs(macAddress, "Device " + message, 3);
  }
  async function updateDeviceCurrentTopic(macAddress, currentTopic, productName)
  {
    let obj = {
      device_topic: currentTopic
    }
    await Device.update(obj, { where: { mac_address: macAddress } }).then(num => {
      console.log(num);
    });


    addProductName(macAddress, productName);
    addLogs(macAddress, "Device Subscribed", 2);
  }
  async function addProductName(macAddress, productName)
  {
    let obj = {
        esp_product_name: productName
    }
    await Device.update(obj, { where: { mac_address: macAddress } }).then(num => {
      console.log(num);
    });
  }
  async function addLogs(macAddress, message, type)
  {
    const device = await Device.findOne({ where: { mac_address: macAddress } });
    if(device)
    {
        var date = new Date();
        await DeviceHistory.create({
            mac_address: device.mac_address, name: device.name,
            topic: device.topic, type: type, user_id: device.user_id,
            device_id: device.id,
            message: message,
            created_at: date.getFullYear()+'-'+(date.getMonth() + 1)+'-'+date.getDate()+' '+date.getHours()+':'+date.getMinutes()+':'+date.getSeconds(),
            updated_at: date.getFullYear()+'-'+(date.getMonth() + 1)+'-'+date.getDate()+' '+date.getHours()+':'+date.getMinutes()+':'+date.getSeconds(),
        });
    }
  }
  async function sendSocketSignal(macAddress, status)
  {
    const device = await Device.findOne({ where: { mac_address: macAddress } });
    if(device)
    {
      io.emit('AWSConnection-' + device.aquarium_id, {mac_address: macAddress, status: status});
    }
  }
  async function sendSocketAck(response)
  {
    const device = await Device.findOne({ where: { mac_address: response.macAddress } });
    console.log(device);
    if(device)
    {
      io.emit('AWSConnection-ack-' + device.aquarium_id, response);
    }
  }
