# plugin-Accessibility
> Plugin de acessibilidade para uso na plataforma Mapas Culturais 

## Efeitos do uso do plugin

- O plugin implementa na plataforma a instalação do Widget V-Libras e também de uma ferramenta de controle de tamanho de fontes e manipulação de contraste.

## Requisitos Mínimos

- Mapas Culturais v7.0.0^

## Configuração básica

### Configurações necessárias para uso no ambiente de desenvolvimento ou produção

- No arquivo `docker/common/config.d/plugins.php`, inserir a linha `'Accessibility',` para configurar o plugin. Veja o exemplo abaixo:

    ```php
    <?php
    use MapasCulturais\Entities;

    return [
        'plugins' => [
            'MultipleLocalAuth' => [ 'namespace' => 'MultipleLocalAuth' ],
            'SamplePlugin' => ['namespace' => 'SamplePlugin'],
            'Accessibility',
        ]
    ];
    ```

### Configurações necessárias para uso no ambiente de desenvolvimento

- No arquivo `dev/docker-compose.yml`, inserir a linha `- ../plugins/Accessibility:/var/www/src/plugins/Accessibility` para mapear o plugin para dentro do container no serviço Mapas. Veja o exemplo abaixo:

    ```yaml
    version: '2'
    services:
      mapas:
        build:
          context: ../
          dockerfile: docker/Dockerfile
        
        command: /var/www/dev/start.sh

        ports:
          - "80:80"
        
        volumes:
          ...

          # temas e plugins
          - ../plugins/SamplePlugin:/var/www/src/plugins/SamplePlugin
          - ../plugins/MultipleLocalAuth:/var/www/src/plugins/MultipleLocalAuth
          - ../plugins/Accessibility:/var/www/src/plugins/Accessibility

        environment:
        
          ...
    ```
