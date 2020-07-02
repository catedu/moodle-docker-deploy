const dotenv = require('dotenv');
const os = require('os');
// const publicIp = require('public-ip');
dotenv.config();
const ovh = require('ovh')({
  appKey: process.env.APP_KEY,
  appSecret: process.env.APP_SECRET,
  consumerKey: process.env.TOKEN
});

(async () => {
  // const ip = await publicIp.v4();
  const interfaces = os.networkInterfaces();
  const ip = interfaces.eno1[0].address;
  const myArgs = process.argv.slice(2);
  if (!myArgs.length) {
    console.log(`exec: node createSubdomain.js https://<myDomain>.aeducar.es`)
    process.exit(1)
  }
  /* check url belongs to aeducar.es */
  const url = myArgs[0].toLowerCase();
  if (url.indexOf(".aeducar.es") === -1) {
    console.log(`exec: node createSubdomain.js https://<myDomain>.aeducar.es`)
    process.exit(1)
  }
  var subDomain = url.replace(/^https?\:\/\//i, '').replace('.aeducar.es', '');
  console.log(`Adding A record ${subDomain} to zone aeducar.es with value ${ip}...`);

  ovh.request('POST', '/domain/zone/aeducar.es/record', {
    fieldType: 'A', // Resource record Name (type: zone.NamedResolutionFieldTypeEnum)
    subDomain, // Resource record subdomain (type: string)
    target: ip, // Resource record target (type: string)
    ttl: 0 // Resource record ttl (type: long)
  }, function (error, credential) {
    if (error) {
      console.log(error)
      process.exit(1)
    }
    console.log(credential);
    ovh.request('POST', '/domain/zone/aeducar.es/refresh', function (error, result) {
      if (error) {
        console.log(error)
        process.exit(1)
      }
      console.log(`DNS refreshed!`);
    });
  });
})();
