const dotenv = require('dotenv');
dotenv.config();
const ovh = require('ovh')({
  endpoint: 'ovh-eu',
  appKey: process.env.APP_KEY,
  appSecret: process.env.APP_SECRET
});

const showCredential = (error, credential) => {
  console.log(error || credential);
}

ovh.request('POST', '/auth/credential', {
  'accessRules': [
    { 'method': 'GET', 'path': '/*' },
    { 'method': 'POST', 'path': '/*' },
    { 'method': 'PUT', 'path': '/*' },
    { 'method': 'DELETE', 'path': '/*' }
  ]
}, showCredential);



