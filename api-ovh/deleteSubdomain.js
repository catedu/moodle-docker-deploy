const dotenv = require('dotenv');
dotenv.config();
const ovh = require('ovh')({
  appKey: process.env.APP_KEY,
  appSecret: process.env.APP_SECRET,
  consumerKey: process.env.TOKEN
});

(async () => {
  const myArgs = process.argv.slice(2);
  if (!myArgs.length) {
    console.log(`exec: node deleteSubdomain.js https://<myDomain>.aeducar.es`)
    process.exit(1)
  }
  /* check url belongs to aeducar.es */
  const url = myArgs[0].toLowerCase();
  if (url.indexOf(".aeducar.es") === -1) {
    console.log(`exec: node deleteSubdomain.js https://<myDomain>.aeducar.es`)
    process.exit(1)
  }
  var subDomain = url.replace(/^https?\:\/\//i, '').replace('.aeducar.es', '');
  console.log(`Removing A record ${subDomain} to zone aeducar.es `);


  ovh.request('GET', '/domain/zone/aeducar.es/record', {
    fieldType: 'A', // Resource record Name (type: zone.NamedResolutionFieldTypeEnum)
    subDomain// Resource record subdomain (type: string)
  }, function (error, data) {

    if (error) {
      console.log(error)
      process.exit(1)
    }
    ovh.request('DELETE', `/domain/zone/aeducar.es/record/${data[0]}`, function (error, credential) {
      if (error) {
        console.log(error)
        process.exit(1)
      }
      ovh.request('POST', '/domain/zone/aeducar.es/refresh', function (error, result) {
        if (error) {
          console.log(error)
          process.exit(1)
        }
        console.log(`DNS refreshed!`);
      });
    });
  });



})();
