# Mapas Culturais Base Project

A ideia desse projeto é facilitar o deploy da plataforma Mapas Culturais e ser um repositório aglutinador das partes do sistema, viabilizando um alto controle das versões de cada uma das peças do sistema (plugins, tema, core do Mapas Culturais, PostgreSQL/PostGIS, redis etc). 

É recomendado a utilização do [Git Flow](https://danielkummer.github.io/git-flow-cheatsheet/index.pt_BR.html) para a estrutura de branches e o [Versionamento Semântico](https://semver.org/lang/pt-BR/) para as tags, da seguinte maneira:

- **branch develop** - para o desenvolvimento de novas funcionalidades e para teste local de novas funcionalidades; 
- **branch master** - para o ambiente de homologaçào, podendo variar para branches específicos de alguma funcionalidade em desenvolvimento para um teste pontual; 
- **tags** para o ambiente de produção (seguindo o _Versionamento Semântico_)


Seguindo a lógica do _Versionamento Semântico_, quando chegar o momento do lançamento da primeira versão em produção, deve ser criada a tag `1.0.0` e a partir daí seguir a seguinte lógica de versionamento:
- Nova **versão PATCH** (ex: `1.0.1`) - quando uma nova configuração for feita, ou se algum bug for corrigido em alguma peça do sistema (ex subir a versão do mapas da versão `5.6.14` para `5.6.15` ou de algum plugin), atualização da versão do postgres, nginx ou redis etc;
- Nova **versão MINOR** (ex: `1.1.0`) - quando uma nova funcionalidade for introduzida ao sistema, um novo plugin ou mudança da versão minor do mapas (ex subir a versão do mapas de `5.6` para `5.7`)
- Nova **versão MAJOR** (ex: `2.0.0`) - quando houver quebra de compatibilidade (ex quando subir a versão do mapas para a versão `6.0`)

## Estrutura de arquivos
- **.env_sample** - modelo para a criação do arquivo `.env`
- **start.sh** - inicializa o ambiente de produçao/homologação
- **restart.sh** - reinicializa o ambiente de produçao/homologação
- **stop.sh** - desliga o ambiente de produçao/homologação
- **update.sh** - atualiza o ambiente de produção
- **logs.sh** - exibe o output do docker-composer de produção, para visualização dos logs
- **bash.sh** - acessa o terminal do container do Mapas
- **psql.sh** - acessa o console psql do banco de dados
- **init-letsencrypt.sh** - script para auxiliar a criação e configuração do certificado _Let's Encrypt_
- **docker-compose.yml** - arquivo de configuração dos serviços utilizados para subir o ambiente de produção/homologação
- **docker-compose-certbot.yml** - arquivo de configuração dos serviços utilizados na geração do certificado _Let's Encript_
- **docker/** - arquivos de configuração e outros utilizados pelo docker-compose
    - **common/config.d/** - arquivos de configuração comuns aos ambientes de desenvolvimento e produção
    - **db/** - arquivo com dump.sql padrão
    - **production/** - arquivos de configuração exclusivos do ambiente de produção
- **dev/** - scripts auxiliares para o desenvolvimento
    - **start.sh** - script que inicializa o ambiente de desenvolvimento
    - **bash.sh** - entra no container da aplicação
    - **shell.sh** - entra no shell do mapas culturais
    - **psql.sh** - entra no banco de dados da aplicação
    - **docker-compose.local.yml** - arquivo de definição do docker-compose utilizado pelos scripts acima
    - **watch.sh** - arquivo para compilar assets do thema atual
- **plugins** - pasta com os plugins desenvolvidos exclusivamente para o projeto
    - **SamplePlugin** - esqueleto de plugin para demostração e para servir de base para o desenvolvimento de outros plugins
- **themes** - pasta com os temas desenvolvidos exclusivaente para o projeto
    - **SampleTheme** - esqueleto de tema filho de Subsite para demostração e para servir de base para o desenvolvimento de outros temas

# Guia rápido para início de novo projeto
Antes de tudo certifique-se de ter os pacotes _git_, _docker_ e _docker-compose_ instalados e estar utilizando sistema operacional Linux ou MacOS. 

_Nos exemplos é usado o comando sudo para que os scripts tenham os privilégios requeridos pelo docker._

### Criando repositório do projeto
Crie um repositório vazio no github ou gitlab (usarei de exemplo o nome _https://github.com/organizacao/meu-mapas_)

Clone o repositório do projeto base no seu computador
```sh
$ git clone https://github.com/mapasculturais/mapasculturais-base-project.git meu-mapas
$ cd meu-mapas
```

Substitua a url do remote origin para a url de seu repositório
```sh
meu-mapas/$ git remote set-url origin https://github.com/organizacao/meu-mapas

# ou, se você tiver sua chave no github
meu-mapas/$ git remote set-url origin git@github.com:organizacao/meu-mapas
```

Dê git push no repositório para enviar a versão inicial para seu repositório vazio.
```sh
meu-mapas/$ git push
To github.com:organizacao/meu-mapas
 * [new branch]      master -> master
```

## Ambiente de desenvolvimento

### Iniciando o ambiente de desenvolvimento
Para subir o ambiente de desenvolvimento basta entrar na pasta `dev` e rodar o script `start.sh`.

```sh
mapacultural/dev/$ sudo ./start.sh
```

acesse no seu navegador http://localhost/

### psysh
Este ambiente roda com o built-in web server do PHP, o que possibilita que seja utilizado o [PsySH](https://psysh.org/]), um console interativo para debug e desenvolvimento. 

no lugar desejado, adicione a linha `eval(\psy\sh());` e você obterá um console. `Ctrl + D` para continuar a execução do código.

### Parando o ambiente de desenvolvimento
Para parar o ambiente de desenvolvimento usar as teclas `Ctrl + C`

### Usuário super administrador da rede
O banco de dados inicial inclui um usuário de role `saasSuperAdmin` de **id** `1` e **email** `Admin@local`.
Este usuário possui permissão de criar, modificar e deletar qualquer objeto do banco.

- **email**: `Admin@local`
- **senha**: `mapas123`

### Testando envio de emails
O ambiente de desenvolvimento inclui o [MailHog](https://github.com/mailhog/MailHog) que pode ser acessado em `http://localhost:8025`.

## Configuração do Tema
### Criando um novo tema
Usaremos para exemplo o nome de tema `NovoTema`

1. Copie a pasta `themes/SampleTheme` para `themes/NovoTema`;
```sh
meu-mapas/themes$ cp -a SamplesTheme NovoTema
```

2. Edite o arquivo `dev/docker-compose.yml` adicionando uma linha na seção _volumes_ para o tema:
```yml
    - ../themes/NovoTema:/var/www/src/themes/NovoTema
```

3. Edite o arquivo `themes/NovoTema/Theme.php` e substitua o namespace (linha 2) por `NovoTema`:
```+PHP
<?php
namespace NovoTema;
```

4. Implemente e estilize o tema. Há um pequeno tutorial de como desenvolver um novo tema, baseado no tema BaseV1, na [documentação para desenvolvedores](https://mapasculturais.gitbook.io/documentacao-para-desenvolvedores/livro-de-receitas/criacao-de-um-tema).

### Adicionando um tema já existente ao projeto
A melhor maneira de adicionar um tema já existente é colocando o repositório deste como submódulo do repositório do projeto. Utilizaremos como exemplo o tema do `SpCultura`, disponível no github.

1. Adicione o repositório do tema como submódulo do repositório do projeto
```sh
meu-mapas/themes git submodule add https://github.com/mapasculturais/theme-SpCultura SpCultura
```

2. Edite o arquivo `dev-scripts/docker-compose.yml` adicionando uma linha na seção _volumes_ para o tema:
```yml
    - ../themes/SpCultura:/var/www/src/themes/SpCultura
```

### Definindo o tema ativo
Edite o arquivo `docker/common/0.main.php` para o ambiente de produção e `dev/0.main.php` para o ambiente de desenvolvimento e defina o valor da chave `themes.active`.
```PHP
    // Define o tema ativo no site principal. Deve ser informado o namespace do tema e neste deve existir uma classe Theme.
    'themes.active' => 'SpCultura',
```

## Configuração dos plugins
### Criando um novo plugin
Usaremos para exemplo o seguinte nome para o plugin: `MeuPlugin`.

1. Copie a pasta `plugins/SamplePlugin` para `plugins/MeuPlugin`;
```sh
meu-mapas/plugins$ cp -a SamplesTheme MeuPlugin
```
2. Edite o arquivo `plugins/MeuPlugin/Plugin.php` e substitua o namespace (linha 2) por `MeuPlugin`:
```PHP
<?php
namespace MeuPlugin;
```
3. Implemente a funcionalidade do plugin. Há um pequeno tutorial de como desenvolver plugins na [documentação para desenvolvedores](https://mapasculturais.gitbook.io/documentacao-para-desenvolvedores/formacao-para-desenvolvedores/plugins).

4. Para o ambiente de desenvolvimento, edite o arquivo `dev/docker-compose.yml` adicionando uma linha na seção _volumes_ para o plugin:
```yml
    - ../plugins/MeuPlugin:/var/www/src/plugins/MeuPlugin
```
Obs.: No ambiente de produção, esse mapeamento é feito atravéz do arquivo Dockerfile em `docker/Dockerfile`

5. Adicione a configuração para habilitar o plugin dentro do array de configuração de plugins no arquivo `docker/common/plugins.php`:
```PHP
<?php

return [
    'plugins' => [
        // .....
        'MeuPlugin' => ['namespace' => 'MeuPlugin', /* 'config' => ['uma-configuracao' => 'valor-da-configuracao'] */],
    ]
];
```

### Adicionando um plugin já existente ao projeto
A melhor maneira de adicionar um plugin já existente é colocando o repositório deste como submódulo do repositório do projeto. Utilizaremos como exemplo o plugin `MetadataKeyword` que serve para configurar metadados que devem ser incluídos na busca por palavra chave das entidades.

1. Adicione o repositório do plugin como submódulo do repositório do projeto
```sh
meu-mapas/plugins$ git submodule add https://github.com/mapasculturais/plugin-MetadataKeyword MetadataKeyword
```

2. Edite o arquivo `dev/docker-compose.yml` adicionando uma linha na seção _volumes_ para o tema:
```yml
    - ../plugins/MetadataKeyword:/var/www/src/plugins/MetadataKeyword
```

3. Adicione a configuração para habilitar o plugin dentro do array de configuração de plugins no arquivo `docker/common/plugins.php`:
```PHP
<?php

return [
    'plugins' => [
        // .....
        'MetadataKeyword' => [
            'namespace' => 'MetadataKeyword', 
            'config' => [
                'agent' => ['En_Municipio', 'En_Nome_Logradouro']
                'space' => ['En_Municipio', 'En_Nome_Logradouro']
            ]
        ],
    ]
];
```

## Ambiente de produção
Antes de montar o ambiente deve-se saber se haverá um load balacer ou um reverse proxy na frente do servidor e se este será responsável por prover o certificado ssl. Caso positivo, pode-se pular as etapa de configuração do certificado Let's Encrypt, indo diretamente para o passo [passo 4](#4-configurando-o-sistema).

Todos os comandos abaixo são executados no servidor onde será instalada a plataforma.

### 1. Clonando o repositório no servidor
Para começar a instalação do ambiente no servidor o primeiro passo é clonar o repositório em alguma pasta do servidor. Uma sugestão é colocá-lo dentro da pasta `/srv`, ou `/var/mapasculturais`

```sh
$ cd /srv

/srv$ sudo clone https://github.com/organizacao/meu-mapas --recursive

/srv$ cd meu-mapas

meu-mapas$
```

### 2. Gerando o certificado Let's Encrypt
Para gerar o certificadao, você precisa editar o arquivo `init-letsencrypt.sh` preenchendo corretamente as linhas que definem as variáveis `domain` e `email`, informando o domínio que aponta para o servidor e preferencialmente um e-mail válido do responsável pelo domínio. Essa configuração deve ficar persistida no repositório, então commite essas modificações.

Após editar o arquivo, atualize o código do servidor e execute o script para testar se a configuração está correta e se o desafio do Let's Encrypt consegue ser executado corretamente.

> IMPORTANTE: o domínio já deve apontar para o servidor e a porta 80 estar aberta para que o desafio do Let's Encript funcione corretamente.

```sh
meu-mapas$ git pull

meu-mapas$ sudo ./init-letsencrypt.sh
```

Tendo um resultado positivo do Let's Encrypt de que a configuração está correta, edite o arquivo `init-letsencrypt.sh` para definir o valor da variável `staging=0` e execute o script novamente:

```sh
meu-mapas$ git pull

meu-mapas$ sudo ./init-letsencrypt.sh
```

> IMPORTANTE: Antes de prosseguir para o próximo passo, certifique-se de que a pasta `docker-data/certs/conf` contém os arquivos abaixo:
- `live/mapasculturais/fullchain.pem`
- `live/mapasculturais/privkey.pem`
- `options-ssl-nginx.conf`
- `ssl-dhparams.pem`

### 3. Preparando o arquivo docker-compose para utilizar o certificado Let's Encrypt:
Para utilizar o certificado Let's Encrypt diretamente no servidor, primeiro deve-se editar o arquivo `docker-compose.yml`, comentar a linha do arquivo de configuração do nginx sem o ssl e descomentar as linha de configuração do nginx que icluem os certificados gerados pelo Let's Encrypt:

```sh
  ##### versão sem ssl
     - ./docker/production/nginx.conf:/etc/nginx/conf.d/default.conf

  ##### versão com ssl
    #  - ./docker/production/nginx-ssl.conf:/etc/nginx/conf.d/default.conf
    #  - ./docker-data/certs/conf:/etc/letsencrypt
    #  - ./docker-data/certs/www:/var/www/certbot
```
> IMPORTANTE: certifique-se de que a identação das linhas descomentadas está correta
### 4. Configurando o sistema
Antes de subir o ambiente é preciso configurá-lo. Para isso crie no servidor um arquivo `.env ` baseado no `.env_sample` e preencha-o corretamente.

```sh
# criando o arquivo
meu-mapas$ cp .env_sample .env

# editando o arquivo (utilize o seu editor preferido)
meu-mapas$ nano .env
```

> IMPORTANTE: Não commitar este arquivo pois contém informações que não devem estar no controle de versão, como chaves e senhas, então este arquivo só deve existir no servidor.


### 4. Subindo o ambiente
Para subir o ambiente basta executar o script `start.sh`.

```sh
meu-mapas$ sudo ./start.sh
```

### 5. Atualizando o ambiente
O repositório vem configurado para utilizar sempre a última versão estável (`latest`) do Mapas Culturais e para atualizá-lo basta executar o script `update.sh`, que fará pull da imagem da última versão estável do core do Mapas Culturais (imagem `hacklab/mapasculturais:latest`), fazer o build da imagem do projeto e reiniciar o docker-compose.

```sh
meu-mapas$ sudo ./update.sh
```

#### Fixando uma versão
Para fixar uma versão do core do Mapas Culturais deve-se editar o arquivos _Dockerfile_ em (`docker/Dockerfile`) e no script `update.sh`.

Por exemplo para fixar na versão `5.6`, deixando atualizar somente versões PATCH dentro da MINOR `5.6`, deve-se modificar a primeira linha dos arquivos Dockerfile como a seguir:

- `docker/Dockerfile`:
```
FROM hacklab/mapasculturais:5.6
```

Deve-se também modificar a linha do `docker pull` no script `update.sh` para que sempre que este seja executado a última versão PATCH dentro da versão MINOR `5.6` seja baixada antes do build:

```sh
docker pull hacklab/mapasculturais:5.6
```

### 6. Backup
Deve ser feito backup ao menos diário de um dump do banco de dados, que pode ser obtido com o script `dump.sh`
```sh
meu-mapas$ sudo ./dump.sh > dump.sql
```
e das pastas abaixo: 
- `docker-data/public-files`
- `docker-data/private-files`
- `docker-data/saas-files`
