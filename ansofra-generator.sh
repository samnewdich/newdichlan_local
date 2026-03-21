./vendor/bin/openapi \
  --bootstrap vendor/autoload.php \
  --output ansofra/public/swagger/src/docs/newdichsrc.json \
  --exclude vendor \
  --exclude apis \
  --exclude phpmyadmin \
  --exclude public \
  --exclude route \
  --exclude Schema \
  --exclude Auth \
  --exclude Cache \
  --exclude Dto \
  --exclude Mail \
  --exclude Middleware \
  --exclude composer.json \
  --exclude composer.lock \
  --exclude index.php \
  --exclude boostrap.php \
  --exclude .htaccess.php \
  --exclude README.txt \
  --exclude nodejs \
  --exclude ansofra \
  src/


./vendor/bin/openapi \
  --bootstrap vendor/autoload.php \
  --output ansofra/public/swagger/docs/newdichapp.json \
  --exclude vendor \
  --exclude apis \
  --exclude phpmyadmin \
  --exclude public \
  --exclude route \
  --exclude Schema \
  --exclude Auth \
  --exclude Cache \
  --exclude Dto \
  --exclude Mail \
  --exclude Middleware \
  --exclude composer.json \
  --exclude composer.lock \
  --exclude index.php \
  --exclude boostrap.php \
  --exclude .htaccess.php \
  --exclude README.txt \
  --exclude nodejs \
  --exclude ansofra \
  app/