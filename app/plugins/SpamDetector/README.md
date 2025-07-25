# plugin-SpamDetector
> Plugin de detecção de spam para uso na plataforma Mapas Culturais

## Efeitos do uso do plugin

- O plugin implementa na plataforma um sistema de detecção automática de spam, identificando termos suspeitos em entidades como Agente, Oportunidade, Projeto, Espaço e Evento, ajudando a manter o conteúdo da plataforma seguro e livre de spam.

## Requisitos Mínimos
- Mapas Culturais v7.0.0^

> **ATENÇÂO:** Até a versão **v7.4.24**, o plugin só permite a customização dos termos de bloqueio através das configurações no arquivo `docker/common/config.d/plugins.php`. A partir da versão **v7.4.25**, é possível customizar e controlar os termos diretamente pela interface.

## Configuração básica

### Configurações necessárias para uso no ambiente de desenvolvimento ou produção

- No arquivo `docker/common/config.d/plugins.php`, adicione a linha `'SpamDetector'` para ativar o plugin:

    ```php
    <?php

    return [
        'plugins' => [
            'MultipleLocalAuth' => [ 'namespace' => 'MultipleLocalAuth' ],
            'SamplePlugin' => ['namespace' => 'SamplePlugin'],
            "SpamDetector",
        ]
    ];
    ```

## Como funciona

- O plugin `SpamDetector` atua de forma contínua monitorando as ações de criação e atualização de entidades no sistema Mapas Culturais, como Agente, Oportunidade, Projeto, Espaço e Evento. Ele realiza a verificação automática de campos previamente configurados, como `name`, `shortDescription`, `longDescription`, entre outros, em busca de termos que possam indicar comportamentos suspeitos ou maliciosos.

- Quando um termo suspeito é encontrado em qualquer um dos campos monitorados, o plugin notifica imediatamente os administradores (super admins e admins) através de e-mail e notificação do sistema. A notificação inclui detalhes sobre os termos detectados e especifica em quais campos esses termos foram encontrados. Ao acessar a entidade, os administradores verão um alerta visível no painel de administração, onde poderão marcar ou desmarcar a entidade como spam. Caso a entidade permaneça classificada como spam, o plugin enviará uma nova notificação a cada 24 horas, reforçando a necessidade de uma possível intervenção.

- Nos casos mais graves, em que um termo bloqueado for identificado, o comportamento do plugin é ainda mais rigoroso. Nessa situação, tanto a entidade quanto o usuário responsável por criá-la ou atualizá-la serão movidos automaticamente para a lixeira, e uma notificação será enviada aos administradores.

## Personalização

> ### Para Mapas Culturais que rodam até a versão v7.4.24
- As configurações do plugin permitem personalizar os termos a serem detectados (`terms`) e bloqueados (`termsBlock`), as entidades monitoradas (`entities`), e os campos onde a detecção deve ocorrer (`fields`). Essas configurações podem ser definidas dentro de uma chave chamada `config` no arquivo `docker/common/config.d/plugins.php`, como mostrado abaixo:

    ```php
    <?php

    return [
        'plugins' => [
            'MultipleLocalAuth' => [ 'namespace' => 'MultipleLocalAuth' ],
            'SamplePlugin' => ['namespace' => 'SamplePlugin'],
            "SpamDetector" => [
                "namespace" => "SpamDetector",
                "config" => [
                    // suas configurações personalizadas abaixo, por exemplo:
                    "terms" => ['compra', 'minecraft', 'venda', 'download'],
                    "termsBlock" => ['citotec', 'apk']
                ]
            ]
        ]
    ];
    ```

- **IMPORTANTE:** Ao adicionar configurações personalizadas na chave `config`, o que for adicionado irá **somar** a configuração padrão. Certifique-se de incluir todos os parâmetros necessários para evitar comportamentos indesejados.


## Notificações

- Quando um possível spam é detectado, uma notificação é enviada aos administradores cadastrados, e um e-mail é gerado usando um template Mustache (`email-spam.html`). O e-mail contém detalhes sobre os termos e os campos onde foram encontrados, juntamente com um link para a entidade suspeita na plataforma.

## Observações
### Controlanto os termos pela interface
> ##### - Disponível apenas para Mapas Culturais que rodam apartir da versão v7.4.25
- Logado como um administrador, acessar o painel de controle e no menu do sidebar esquerdo existrá um botão que ao ser clicado abrirá um modal. Neste modal é possivel adicionar ou remover termos das duas lista **Notificação** ou **Bloqueio**.


