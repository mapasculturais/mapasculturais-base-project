# Mapas Culturais Base Project
Este é um projeto base para desenvolvimento de temas e plugins.

## Estrutura de arquivos
- **compose**
    - **common** - arquivos comuns dos ambientes de desenvolvimento e produção
    - **local** - arquivos exclusivamente para o ambiente de desenvolvimento
    - **production** - arquivos exclusivamente para o ambiente de produção
- **dev-scripts** - scripts auxiliares para o desenvolvimento
    - **start-dev.sh** - script que inicializa o ambiente de desenvolvimento
    - **bash.sh** - entra no container da aplicação
    - **shell.sh** - entra no shell do mapas culturais
    - **psql.sh** - entra no banco de dados da aplicação
    - **docker-compose.local.yml** - arquivo de definição do docker-compose utilizado pelos scripts acima
- **plugins** - pasta com os plugins desenvolvidos exclusivamente para o projeto
    - **SamplePlugin** - esqueleto de plugin para demostração e para servir de base para o desenvolvimento de outros plugins
- **themes** - pasta com os temas desenvolvidos exclusivaente para o projeto
    - **SampleTheme** - esqueleto de tema filho de Subsite para demostração e para servir de base para o desenvolvimento de outros temas

## Guia rápido para início de novo projeto
Antes de tudo certifique-se de ter os pacotes _git_, _docker_ e _docker-compose_ instalados e estar utilizando sistema operacional Linux ou MacOS. 

_Nos exemplos é usado o comando sudo para que os scripts tenham os privilégios requeridos pelo docker._

### Criando repositório do projeto
Crie um repositório vazio no github ou gitlab (usarei de exemplo o nome _https://github.com/organizacao/meu-mapas_)

Clone o repositório do projeto base no seu computador
```
$ git clone https://github.com/mapasculturais/mapasculturais-base-project.git meu-mapas
$ cd meu-mapas
```

Substitua a url do remote origin para a url de seu repositório
```
meu-mapas/$ git remote set-url origin https://github.com/organizacao/meu-mapas

# ou, se você tiver sua chave no github
meu-mapas/$ git remote set-url origin git@github.com:organizacao/meu-mapas

```

Dê git push no repositório para enviar a versão inicial para seu repositório vazio.
```
meu-mapas/$ git push
To github.com:organizacao/meu-mapas
 * [new branch]      master -> master

```

### Ambiente de desenvolvimento

#### Iniciando o ambiente de desenvolvimento
Para subir o ambiente de desenvolvimento basta entrar na pasta `dev-scripts` e rodar o script `start-dev.sh`.

```
meu-mapas/dev-scripts/$ sudo ./start-dev.sh
```

acesse no seu navegador http://localhost/

#### psysh
Este ambiente roda com o built-in web server do PHP, o que possibilita que seja utilizado o [PsySH](https://psysh.org/]), um console interativo para debug e desenvolvimento. 

no lugar desejado, adicione a linha `eval(\psy\sh());` e você obterá um console. `Ctrl + D` para continuar a execução do código.

#### Parando o ambiente de desenvolvimento
Para parar o ambiente de desenvolvimento usar as teclas `Ctrl + C`

#### Usuário super administrador da rede
O banco de dados inicial inclui um usuário de role `saasSuperAdmin` de **id** `1` e **email** `Admin@local`.
Este usuário possui permissão de criar, modificar e deletar qualquer objeto do banco.

- **email**: `Admin@local`
- **senha**: `mapas123`

## Criando um novo tema
Usaremos para exemplo o nome de tema `NovoTema`

1. copie a pasta `themes/SampleTheme` para `themes/NovoTema`;
```
meu-mapas/themes$ cp -a SamplesTheme NovoTema
```
2. edite o arquivo `dev-scripts/docker-compose.yml` adicionando uma linha na seção _volumes_ para o tema:
```
    - ../themes/NovoTema:/var/www/html/protected/application/themes/NovoTema
```
3. edite o arquivo `themes/NovoTema/Theme.php` e substitua o namespace (linha 2) por `NovoTema`:
```+PHP
<?php
namespace NovoTema;
```

## Criando um novo plugin
Usaremos para exemplo o seguinte nome para o plugin: `MeuPlugin`

1. copie a pasta `plugins/SamplePlugin` para `plugins/MeuPlugin`;
```
meu-mapas/plugins$ cp -a SamplesTheme MeuPlugin
```
2. edite o arquivo `dev-scripts/docker-compose.yml` adicionando uma linha na seção _volumes_ para o tema:
```
    - ../plugins/MeuPlugin:/var/www/html/protected/application/plugins/MeuPlugin
```
3. edite o arquivo `plugins/MeuPlugin/Plugin.php` e substitua o namespace (linha 2) por `MeuPlugin`:
```+PHP
<?php
namespace MeuPlugin;
```
