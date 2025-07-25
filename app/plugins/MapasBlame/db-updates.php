<?php

use function MapasCulturais\__exec;
use function MapasCulturais\__table_exists;
use function MapasCulturais\__try;

return [
    'create table blame tables' => function () {
        if (!__table_exists('blame_request')) {
            __exec("
                CREATE TABLE blame_request (
                    id CHAR(13) NOT NULL,
                    ip CHAR(15) NOT NULL,
                    session_id CHAR(32) NOT NULL,
                    user_id INT DEFAULT NULL, 
                    user_agent VARCHAR(255) NULL,
                    user_browser_name VARCHAR(32) NULL,
                    user_browser_version VARCHAR(24) NULL,
                    user_os VARCHAR(16),
                    user_device VARCHAR(16),
                    metadata JSON DEFAULT '{}' NOT NULL, 
                    created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT now(), 
                    
                PRIMARY KEY(id))");

                __exec("CREATE INDEX IDX_blame_log__ip ON blame_request (ip)");
                __exec("CREATE INDEX IDX_blame_request__session_id ON blame_request (session_id)");
                __exec("CREATE INDEX IDX_blame_request__user_id ON blame_request (user_id)");

                __exec("CREATE INDEX IDX_blame_request__user_browser_name ON blame_request (user_browser_name)");
                __exec("CREATE INDEX IDX_blame_request__user_os ON blame_request (user_os)");
                __exec("CREATE INDEX IDX_blame_request__user_device ON blame_request (user_device)");
                                
        }

        if (!__table_exists('blame_log')) { 
            __exec("CREATE SEQUENCE blame_log_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
            __exec("
                CREATE TABLE blame_log (
                    id INT NOT NULL DEFAULT nextval('blame_log_id_seq'),
                    request_id CHAR(13) NOT NULL,
                    action VARCHAR(255) NOT NULL,
                    metadata JSON DEFAULT '{}' NOT NULL, 
                    created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT now(), 
                    
                PRIMARY KEY(id))");
            __exec("CREATE INDEX IDX_blame_log__action ON blame_log (action)");
            __exec("CREATE INDEX IDX_blame_log__created_at ON blame_log (created_at)");

            __exec("
                ALTER TABLE blame_log 
                ADD CONSTRAINT FK_blame_log_request 
                FOREIGN KEY (request_id) 
                REFERENCES blame_request (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        }
    },
    'alter table blame_log action size' => function(){
        __try("DROP VIEW blame");
        __exec("ALTER TABLE blame_log ALTER COLUMN action TYPE varchar(2048)");
        __exec("DELETE FROM db_update WHERE name = 'create view blame'");
    },

    'alter table blame_request columns length again' => function(){
        __try("DROP VIEW blame");
        
        __exec("ALTER TABLE blame_request ALTER COLUMN user_os TYPE varchar(512)");
        __exec("ALTER TABLE blame_request ALTER COLUMN user_device TYPE varchar(512)");
        __exec("ALTER TABLE blame_request ALTER COLUMN user_browser_name TYPE varchar(512)");
        __exec("ALTER TABLE blame_request ALTER COLUMN user_browser_version TYPE varchar(512)");
        __exec("ALTER TABLE blame_request ALTER COLUMN user_agent TYPE varchar(1024)");
        
        __exec("DELETE FROM db_update WHERE name = 'create view blame'");
    },
    

    'create view blame' => function () {
        __exec("CREATE VIEW blame AS (
            SELECT 
                bl.id AS log_id,
                br.id AS request_id,
                br.ip,
                br.session_id,
                br.user_id,
                bl.action,
                br.user_agent,
                br.user_browser_name,
                br.user_browser_version,
                br.user_os, 
                br.user_device,
                br.metadata AS request_metadata,
                bl.metadata AS log_metadata,
                br.created_at AS request_ts,
                bl.created_at AS log_ts
            FROM 
                blame_request br
                LEFT JOIN blame_log bl ON bl.request_id = br.id 
            )");
    },
];