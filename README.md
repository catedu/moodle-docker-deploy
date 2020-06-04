# moodle-docker-deploy

Arquitecture de deploy moodle containers

## Customize deploy

1. Copy .env-sample to .env in root directory and customize creation new moodle's to your infraestrcture (mandatory)

2. Copy .env-sample to .env in nginx-proxy customize your proxy and phpmyadmin connection (mandatory)

3. To create DNS dinamic (api-ovh), git clone the repository, copy .env-sample to .env and customize your API connection with OVH. (not mandatory)