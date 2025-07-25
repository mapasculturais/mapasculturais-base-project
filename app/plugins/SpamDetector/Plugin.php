<?php

namespace SpamDetector;

use DateTime;
use Mustache;
use MapasCulturais\i;
use MapasCulturais\App;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Event;
use MapasCulturais\Entities\Space;
use MapasCulturais\Entities\Project;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Notification;
use MapasCulturais\Entity;
use SpamDetector\Controller;

class Plugin extends \MapasCulturais\Plugin
{
    protected static $instance;

    public function __construct($config = [])
    {
        $spam_terms = [];
        $default_terms = [];
        $terms_block = [];
        
        $spam_terms = Plugin::getFileTerms();
        $default_terms = $spam_terms['notification'];
        $terms_block = $spam_terms['blocked'];

        if(isset($config['termsBlock'])) {
            $terms_block = $terms_block += $config['termsBlock'];
            $config['termsBlock'] = $terms_block;
        }
        
        if(isset($config['terms'])) {
            $default_terms = $default_terms += $config['terms'];
            $config['terms'] = $default_terms;
        }

        $default_fields = [
            'name', 
            'shortDescription', 
            'longDescription', 
            'nomeSocial', 
            'nomeCompleto', 
            'comunidadesTradicionalOutros',
            'facebook',
            'twitter',
            'instagram',
            'linkedin',
            'vimeo',
            'spotify',
            'youtube',
            'pinterest',
            'tiktok'
        ];

        $config += [
            'terms' => env('SPAM_DETECTOR_TERMS', $default_terms),
            'entities' => env('SPAM_DETECTOR_ENTITIES', ['Agent', 'Opportunity', 'Project', 'Space', 'Event']),
            'fields' => env('SPAM_DETECTOR_FIELDS', $default_fields),
            'termsBlock' => env('SPAM_DETECTOR_TERMS_BLOCK', $terms_block),
        ];

        parent::__construct($config);
        self::$instance = $this;
    }

    public function _init()
    {
        $app = App::i();
        $plugin = $this;

        $hooks = implode('|', $plugin->config['entities']);
        $last_spam_sent = null;

        $app->hook('GET(<<auth|panel>>.<<*>>):before', function() use ($app) {
            $app->view->enqueueStyle('app-v2', 'SpamDetector-v2', 'css/plugin-SpamDetector.css');
        });

        $app->hook("entity(<<{$hooks}>>).save:before", function () use ($plugin, $app) {
            /** @var Entity $this */
            if($plugin->getSpamTerms($this, $plugin->config['termsBlock']) && $this->spam_status != 2) {
                $this->spamBlock = true;
            }
        });

        $app->hook('template(panel.<<*>>.panel-nav-left-sidebar):begin', function() use($app) {
            if($app->user->is('admin')) {
                $this->part('configuration-menu');
            }
        });
        
        // Verifica se existem termos maliciosos e dispara o e-mail e a notificação
        $app->hook("entity(<<{$hooks}>>).save:after", function () use ($plugin, $last_spam_sent, $app) {
            /** @var Entity $this */
            $users = $plugin->getAdminUsers($this);
            $terms = array_merge($plugin->config['termsBlock'], $plugin->config['terms']);

            $spam_terms = $plugin->getSpamTerms($this, $terms);
            $current_date_time = new DateTime();
            $current_timestamp = $current_date_time->getTimestamp();
            $eligible_spam = $last_spam_sent ?? $this->spam_sent_email;

            $is_spam_eligible = !$eligible_spam || ($current_timestamp - $eligible_spam->getTimestamp()) >= 86400;

            $conn = $app->em->getConnection();
            $table = $plugin->dictTable($this);

            if ($spam_terms && $is_spam_eligible && $this->spam_status != 2) {
                $ip = $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';

                foreach ($users as $user) {
                    $plugin->createNotification($user->profile, $this, $spam_terms, $ip);
                }

                $dict_entity = $plugin->dictEntity($this, 'artigo');
                $message = i::__("{$dict_entity} {$this->name} foi enviado para moderação. Informamos que registramos seu ip: {$ip}");
                $notification = new Notification;
                $notification->user = $this->ownerUser;
                $notification->message = $message;
                $notification->save(true);


                if($spam_terms) {
                    $table_meta = strtolower($table)."_meta";
                    if(!$conn->fetchAll("SELECT * FROM {$table_meta} WHERE key = 'spam_status' and object_id = {$this->id}")) {
                        $conn->executeQuery("INSERT INTO {$table_meta} (id, object_id, key, value) VALUES (nextval('{$table_meta}_id_seq'), {$this->id}, 'spam_status', 1)");
                    }
                }
            }

            if($this->spamBlock) {
                $conn->executeQuery("UPDATE {$table} SET status = -10 WHERE id = {$this->id}");
                $plugin->lockEntityTree($this->ownerUser);
            }
        });

        // Garante que o termo encontrado fique salvo e o e-mail seja disparado
        $app->hook("entity(<<{$hooks}>>).save:finish", function () use ($plugin, $app) {
            /** @var Entity $this */
            if($plugin->getSpamTerms($this, $plugin->config['termsBlock']) && $this->spam_status != 2) {
                $this->ownerUser->setStatus(-10);
            }
        });

        // remove a permissão de publicar caso encontre termos que estão na lista de termos elegível a bloqueio
        $app->hook("entity(<<{$hooks}>>).canUser(publish)", function ($user, &$result) use($plugin, &$last_spam_sent) {
            /** @var Entity $this */
            if($plugin->getSpamTerms($this, $plugin->config['termsBlock']) && !$user->is('admin') && $this->spam_status != 2) {
                $result = false;
            }
        });

        // Caso for encontrado o termo e o usuário logado for o admin, irá aparecer na entidade um warning
        $app->hook("template(<<{$hooks}>>.<<edit|single>>.entity-header):before", function() use($plugin, $app) {
            $entity = $this->controller->requestedEntity;
            $terms = array_merge($plugin->config['termsBlock'], $plugin->config['terms']);

            if($plugin->getSpamTerms($entity, $terms) && $app->user->is('admin')) {
                $this->part('admin-spam-warning');
                $app->view->enqueueStyle('app-v2', 'admin-spam-warning', 'css/admin-spam-warning.css');
            }
        });

        $app->hook('component(mc-icon).iconset', function(&$iconset){
            $iconset['security'] = "material-symbols:security";
        });
    }
    
    public function register() {
        $app = App::i();

        $app->registerController('spamdetector', Controller::class);

        $entities = $this->config['entities'];

        foreach($entities as $entity) {
            $namespace = "MapasCulturais\\Entities\\{$entity}";

            $this->registerMetadata($namespace,'spam_sent_email', [
                'label' => i::__('Data de envio do e-mail'),
                'type' => 'DateTime',
                'default' => null,
            ]);
            
            $this->registerMetadata($namespace,'spam_status', [
                'label' => i::__('Classificar como Spam'),
                'type' => 'int',
                'default' => 1,
                'unserilize' => function($entity) {
                    if(!$entity->spam_status) {
                        return 1;
                    }

                    return $entity->spam_status;
                }
            ]);
        }
    }
    
    public function createNotification($recipient, $entity, $spam_detections, $ip)
    {
        $app = App::i();
        $app->disableAccessControl();
        
        $is_save = !$entity->spamBlock;
        $message = $this->getNotificationMessage($entity, $is_save);
        $notification = new Notification;
        $notification->user = $recipient->user;
        $notification->message = $message;
        $notification->save(true);
        
        $filename = $app->view->resolveFilename("views/emails", "email-spam.html");
        $template = file_get_contents($filename);
        
        $field_translations = [
            "name" => i::__("Nome"),
            "shortDescription" => i::__("Descrição Curta"),
            "longDescription" => i::__("Descrição Longa"),
        ];
        
        $detected_details = [];
        foreach ($spam_detections as $detection) {
            $translated_field = isset($field_translations[$detection['field']]) ? $field_translations[$detection['field']] : $detection['field'];
            $detected_details[] = "Campo: $translated_field, Termos: " . implode(', ', $detection['terms']) . '<br>';
        }

        $dict_entity = $this->dictEntity($entity, 'artigo');

        $mail_notification_message = i::__('O sistema detectou possível spam em um conteúdo recente. Por favor, revise as informações abaixo e tome as medidas necessárias:');
        $mail_blocked_message = i::__("O sistema detectou um conteúdo inadequado neste cadastro e moveu-o para a lixeira. Seguem abaixo os dados para análise do conteúdo:");
        $mail_message = $is_save ? $mail_notification_message : $mail_blocked_message;

        $params = [
            "siteName" => $app->siteName,
            "nome" => $entity->name,
            "id" => $entity->id,
            "url" => $entity->singleUrl,
            "baseUrl" => $app->getBaseUrl(),
            "detectedDetails" => implode("\n", $detected_details),
            "ip" => $ip,
            "adminName" => $recipient->name,
            'mailMessage' => $mail_message,
            'dictEntity' => $this->dictEntity($entity, 'none')
        ];
        
        $mustache = new \Mustache_Engine();
        $content = $mustache->render($template, $params);

        if ($email = $this->getAdminEmail($recipient)) {
            $app->createAndSendMailMessage([
                'from' => $app->config['mailer.from'],
                'to' => $email,
                'subject' => $is_save ? i::__("Spam - Conteúdo suspeito") : i::__("Spam - {$dict_entity} foi bloqueado(a)"),
                'body' => $content,
            ]);
        }

        // Salvar metadado
        $date_time = new DateTime();
        $date_time->add(new \DateInterval('PT10S'));
        $date_time = $date_time->format('Y-m-d H:i:s');
        
        $table = $this->dictTable($entity);
        $table_meta = strtolower($table)."_meta";

        $conn = $app->em->getConnection();
        if(!$conn->fetchAll("SELECT * FROM {$table_meta} WHERE key = 'spam_sent_email' and object_id = {$entity->id}")) {
            $conn->executeQuery("INSERT INTO {$table_meta} (id, object_id, key, value) VALUES (nextval('{$table_meta}_id_seq'), {$entity->id}, 'spam_sent_email', '{$date_time}')");
        } else {
            $conn->executeQuery("UPDATE {$table_meta} SET value = '{$date_time}' WHERE object_id = {$entity->id} AND key = 'spam_sent_email'");
        }

        $app->enableAccessControl();
    }   

    /**
     *  Retorna o texto relacionado a entidade
     * @param Entity $entity 
     * @return string 
     */
    public function dictEntity(Entity $entity, $type = "preposição"): string
    {
        $class = $entity->getClassName();

        switch ($type) {
            case 'preposição':
                $prefixes = (object) ["f" => "na", "m" => "no"];
                break;
            case 'pronome':
                $prefixes = (object) ["f" => "esta", "m" => "este"];
                break;
            case 'artigo':
                $prefixes = (object) ["f" => "a", "m" => "o"];
                break;
            case 'none':
                $prefixes = (object) ["f" => "", "m" => ""];
                break;
            default:
                $prefixes = (object) ["f" => "", "m" => ""];
                break;
        }

        $entities = [
            Agent::class => "{$prefixes->m} Agente",
            Opportunity::class => "{$prefixes->f} Oportunidade",
            Project::class => "{$prefixes->m} Projeto",
            Space::class => "{$prefixes->m} Espaço",
            Event::class => "{$prefixes->m} Evento",
        ];

        return $entities[$class];
    }

    /**
     *  Retorna o texto com o nome da tabela
     * @param Entity $entity 
     * @return string 
     */
    public function dictTable(Entity $entity): string
    {
        $class = $entity->getClassName();

        $entities = [
            Agent::class => "agent",
            Opportunity::class => "opportunity",
            Project::class => "project",
            Space::class => "space",
            Event::class => "event",
        ];

        return $entities[$class];
    }

    /**
     * @param string $text
     * @return string
     */
    public function formatText($text)
    {
        $text = trim($text);
        $text = strip_tags($text);
        $text = mb_strtolower($text);

        return $text;
    }

    /**
     * @param object $entity Objeto da entidade que deve ter a propriedade `subsiteId`. A presença desta propriedade determina o tipo de papéis a serem recuperados.
     * 
     * @return array Um array contendo os IDs dos usuários que têm um papel administrativo. O array pode estar vazio se nenhum papel administrativo for encontrado.
    */
    public function getAdminUsers($entity): array {
        $app = App::i();

        $roles = $app->repo('Role')->findBy(['subsiteId' => [$entity->subsiteId, null]]);
        
        $users = [];
        if ($roles) {
            foreach ($roles as $role) {
                if ($role->user->is('admin')) {
                    $users[] = $role->user;
                }
            }
        }

        return $users;
    }

    /**
     * @param object $entity Objeto da entidade a ser validada. A entidade deve ter propriedades que correspondem aos campos configurados.
     * 
     * @return array Retorna um array contendo os campos onde termos de spam foram encontrados.
    */
    public function getSpamTerms($entity, $terms): array {
        $app = App::i();

        $fields = $this->config['fields'];
        $spam_detector = [];
        $found_terms = [];
        $special_chars = ['@', '#', '$', '%', '^', '·', '&', '*', '(', ')', '-', '_', '=', '+', '{', '}', '[', ']', '|', ':', ';', '"', '\'', '<', '>', ',', '.', '?', '/', ' '];
        $special_chars = array_map(fn($char) => preg_quote($char, '/'), $special_chars);
        $special_chars = '[' . implode('', $special_chars) . ']*';

        foreach ($fields as $field) {
            if ($value = $entity->$field) {
                $lowercase_value = $this->formatText($value);
                
                foreach ($terms as $term) {
                    $lowercase_term = $this->formatText($term);
                    $_term = implode("{$special_chars}", mb_str_split($lowercase_term));

                    $pattern = '/([^\w]|[_0-9]|^)' . $_term . '([^\w]|[_0-9]|$)/';
                    
                    if (preg_match($pattern, $lowercase_value) && !in_array($term, $found_terms)) {
                        $found_terms[$field][] = $term;
                    }
                }
            }
        }

        if ($found_terms) {
            foreach($found_terms as $key => $value) {
                $spam_detector[] = [
                    'field' => $key,
                    'terms' => $value
                ];
            }
        }

        return $spam_detector;
    }

    /**
     * @param object $entity Objeto da entidade que contém as propriedades `name` e `singleUrl`. A propriedade `name` é usada para identificar a entidade na mensagem, e `singleUrl` é o link para a verificação.
     * @param bool $is_save Indica o status de salvamento da entidade.
     * 
     * @return string Retorna uma mensagem formatada de notificação baseada no status de salvamento.
    */
    public function getNotificationMessage($entity, $is_save): string {
        $dict_entity = $this->dictEntity($entity, 'artigo');
        $message_save = i::__("Possível spam detectado {$dict_entity} - <strong><i>{$entity->name}</i></strong><br><br> <a href='{$entity->singleUrl}'>Clique aqui</a> para verificar. Mais detalhes foram enviados para o seu e-mail");
        $message_insert = $message_insert = i::__("Possível spam detectado {$dict_entity} - <strong><i>{$entity->name}</i></strong><br><br> Apenas um administrador pode publicar este conteúdo, <a href='{$entity->singleUrl}'>clique aqui</a> para verificar. Mais detalhes foram enviados para o seu e-mail");

        $message = $is_save ? $message_save : $message_insert;

        return $message;
    }
    
    /**
     * @param object $agent Objeto que representa o agente. O objeto deve ter as propriedades `emailPrivado`, `emailPublico`, e `user` (que deve ter a propriedade `email`).
     * 
     * @return string O endereço de e-mail do agente.
    */
    public function getAdminEmail($recipient): string {
        if($recipient->emailPrivado) {
            $email = $recipient->emailPrivado;
        } else if($recipient->emailPublico) {
            $email = $recipient->emailPublico;
        } else {
            $email = $recipient->user->email;
        }

        return $email;
    }

    public static function getInstance(){
        return self::$instance;
    }
    
    /**
     * @return array Retorna um array com os dados salvos no arquivo de configuração de termos
     */
    public static function getFileTerms(): array
    {
        $path = Plugin::getPathFile();
        $result = [
            "notification" => [],
            "blocked" => [],
        ];

        if (file_exists($path)) {
            $data = file_get_contents($path);

            if($_data = json_decode($data, true)) {
                $result['notification'] = $_data['notification'] ?? [];
                $result['blocked'] = $_data['blocked'] ?? [];
            }
        }

        return $result;
    }

    /**
     * @return string Retorna uma string que representa o caminho do arquivo de configuração de termos
     */
    public static function getPathFile(): string
    {
        $file_path = PRIVATE_FILES_PATH . "spamDetector";
        $file_name = 'terms-config.txt';
        $path = $file_path . '/' . $file_name;
        $source_file = __DIR__ . '/files/' . $file_name;

        // Verifica se o diretório existe, senão cria
        if (!is_dir($file_path)) {
            mkdir($file_path, 0777, true);
        }

        // Verifica se o arquivo não existe e copia do diretório de origem
        if (!file_exists($path) && file_exists($source_file)) {
            copy($source_file, $path);
        }

        return $path;
    }

    public function lockEntityTree($user)
    {
        $app = App::i();

        $conn = $app->em->getConnection();

        $agent_ids = [];
        if($entities = $this->config['entities']) {
            if($results = $conn->fetchAll("SELECT * FROM agent WHERE user_id = {$user->id}")) {
                foreach($results as $value) {
                    $agent_ids[] = $value['id'];
                }
            }

            if($agent_ids) {
                $ids = implode(",", $agent_ids);
                foreach($entities as $entity) {
                    $table = strtolower($entity);
                    $column = $table === "agent" ? "id" : "agent_id";
                    $conn->executeQuery("UPDATE {$table} SET status = -10 WHERE {$column} IN ({$ids})");
                }
            }
        }
    }
}
