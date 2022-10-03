# News website developed by Roman Chekhaniuk.

Instruction to install:
- Copy repo
- Enter next commands:
  - make docker-upd
  - make docker-shell
  - composer install
  - drush sql-cli < backups/backup.sql
  - apk add nodejs
  - apk add apm
  - npm install -g gulp-cli
  - npm install
  - gulp --production
  - drush cr


