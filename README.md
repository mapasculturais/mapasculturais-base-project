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
Certifique-se de ter os pacotres _git_, _docker_ e _docker-compose_ instalados e estar utilizando sistema operacional Linux ou MacOS. 

Crie um repositório vazio no github ou gitlab (usarei de exemplo o nome _https://github.com/organizacao/meu-mapas_)

Clone o repositório do projeto base
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

Para subir o ambiente de desenvolvimento basta entrar na pasta `dev-scripts` e rodar o script `dev-start.php`.
```
meu-mapas/dev-scripts/$ sudo ./start-dev.php
```

Para parar o ambiente de desenvolvimento usar as teclas `Ctrl+c`

## Criando um novo tema
Usaremos para exemplo o nome de tema `NovoTema`

1. copie a pasta `themes/SampleTheme` para `themes\NovoTema`;
2. edite o arquivo `dev-scripts/docker-compose.yml` adicionando uma linha na seção _volumes_ para o tema:
```
    - ../themes/NovoTema:/var/www/html/protected/application/themes/NovoTema
```
3. edite o arquivo `themes\NovoTema\Theme.php` e substitua o namespace (linha 2) por `NovoTema`:
```+PHP
<?php
namespace NovoTema;
```


