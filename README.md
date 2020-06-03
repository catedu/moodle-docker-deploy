# moodle-docker-deploy

Arquitecture de deploy moodle containers

## Customize deploy

1. Copy .env-sample to .env in root directory and customize creation new moodle's to your infraestrcture (mandatory)

2. Copy .env-sample to .env in nginx-proxy customize your proxy and phpmyadmin connection (mandatory)

3. Copy .env-sample to .env in apiOVH and customize your API connection with OVH, to create DNS Records if its necessary (not mandatory)