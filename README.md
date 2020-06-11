# moodle-docker-deploy

Arquitecture de deploy moodle containers

## Customize new deploy

1. Copy .env-sample to .env in root directory and customize creation new moodle's to your infraestrcture

2. Copy .env-sample to .env in nginx-proxy customize your proxy and phpmyadmin connection

3. To create DNS dinamic with OVH provider (api-ovh), you can use api-ovh. Copy .env-sample to .env and customize your API connection with OVH. (not mandatory)

4. Think and create a template directory (with this name) to deploy

5. Run createMoodle.sh with this template

## Update Upgrade deploy

1. Think and create a template directory to update your deploy

2. Copy env-sample-update to .env-update and customize new variables to add or modify variables (not mandatory)

3. Run upgradeMoodle over your deploy using the template

Note: The update/upgrade process make a backup of all data of the deploy in the root directoy