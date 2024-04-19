#!/bin/bash

if ! [ -x "$(command -v docker-compose)" ]; then
  echo 'Error: docker-compose is not installed.' >&2
  exit 1
fi

echo "EDITE ESTE ARQUIVO E DEFINA AS VARIÁVEIS domain, email e staging"
exit; # E APAGUE ESSA LINHA

# Domínio da instalação
domain=(meumapa.gov.br)

# Informe um e-mail válido
email="webmaster@meumapa.gov.br"

# EVITA que se atinja o LIMITE DE REQUESTS ao Let's Encrypt enquanto se testa as configurações
# defina stagin=0 quando os testes passarem e execute novamente o script
staging=1 

data_path="./docker-data/certbot"
rsa_key_size=4096

if [ -d "$data_path" ]; then
  read -p "Existing data found for $domain. Continue and replace existing certificate? (y/N) " decision
  if [ "$decision" != "Y" ] && [ "$decision" != "y" ]; then
    exit
  fi
fi

if [ ! -e "$data_path/conf/options-ssl-nginx.conf" ] || [ ! -e "$data_path/conf/ssl-dhparams.pem" ]; then
  echo "### Downloading recommended TLS parameters ..."
  mkdir -p "$data_path/conf"
  curl -s https://raw.githubusercontent.com/certbot/certbot/master/certbot-nginx/certbot_nginx/_internal/tls_configs/options-ssl-nginx.conf > "$data_path/conf/options-ssl-nginx.conf"
  curl -s https://raw.githubusercontent.com/certbot/certbot/master/certbot/certbot/ssl-dhparams.pem > "$data_path/conf/ssl-dhparams.pem"
  echo
fi

echo "### Creating dummy certificate for $domain ..."
path="/etc/letsencrypt/live/mapasculturais"
mkdir -p "$data_path/conf/live/mapasculturais"
docker compose -f docker-compose.certbot.yml run --rm --entrypoint "\
  openssl req -x509 -nodes -newkey rsa:1024 -days 1\
    -keyout '$path/privkey.pem' \
    -out '$path/fullchain.pem' \
    -subj '/CN=localhost'" certbot
echo


echo "### Starting nginx ..."
docker compose -f docker-compose.certbot.yml up --force-recreate -d nginx
echo

echo "### Deleting dummy certificate for $domain ..."
docker compose -f docker-compose.certbot.yml run --rm --entrypoint "\
  rm -Rf /etc/letsencrypt/live/mapasculturais && \
  rm -Rf /etc/letsencrypt/archive/mapasculturais && \
  rm -Rf /etc/letsencrypt/renewal/mapasculturais.conf" certbot
echo


echo "### Requesting Let's Encrypt certificate for $domain ..."
#Join $domain to -d args
domain_args=""
for domain in "${domains[@]}"; do
  domain_args="$domain_args -d $domain"
done

# Select appropriate email arg
case "$email" in
  "") email_arg="--register-unsafely-without-email" ;;
  *) email_arg="--email $email" ;;
esac

# Enable staging mode if needed
if [ $staging != "0" ]; then staging_arg="--staging"; fi

docker compose -f docker-compose.certbot.yml run --rm --entrypoint "\
  certbot certonly --webroot -w /var/www/certbot \
    $staging_arg \
    $email_arg \
    $domain_args \
    --rsa-key-size $rsa_key_size \
    --agree-tos \
    --force-renewal" certbot
echo

echo "### baixando seviços"
docker compose -f docker-compose.certbot.yml down

rm -rf docker-data/certs
mv docker-data/certbot docker-data/certs
mv docker-data/certs/conf/live/$domain docker-data/certs/conf/live/mapasculturais