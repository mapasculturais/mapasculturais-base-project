<?php 
$this->layout = 'panel'; 
$app->view->enqueueStyle('app', 'multipleLocal-govbr', 'css/govbr.css');

?>

<div class="panel-list panel-main-content">
    
    <?php if($feedback_msg): ?>

        <div class="auth_feedback <?php echo $feedback_success ? 'success' : 'error'; ?>">
            <?php echo htmlentities($feedback_msg); ?>
        </div>

    <?php endif; ?>
    
    <form action="<?php echo $form_action; ?>" method="POST">
        <div class="auth-row general_field">
            <input type="submit" value="<?php \MapasCulturais\i::esc_attr_e('Guardar alteraçoes', 'multipleLocal'); ?>" />  
            <h2><?php \MapasCulturais\i::_e('Trocar e-mail', 'multipleLocal'); ?></h2>

            <div>
                <label> 
                    <?php \MapasCulturais\i::_e('Email', 'multipleLocal'); ?> 
                    <input type="text" name="email" value="<?php echo htmlentities($email); ?>" />
                </label>
            </div>
            
            <div>
                <h2><?php \MapasCulturais\i::_e('Trocar Senha', 'multipleLocal'); ?></h2>
                <div>
                    <label> 
                        <?php \MapasCulturais\i::_e('Senha atual', 'multipleLocal'); ?>
                        <input type="password" name="current_pass" value="" />
                    </label>
                </div>

                <div>
                    <label> 
                        <?php \MapasCulturais\i::_e('Nova senha', 'multipleLocal'); ?>
                        <input type="password" name="new_pass" value="" />
                    </label>
                </div>
            </div>

            <div>
                <label> 
                    <?php \MapasCulturais\i::_e('Confirmar nova senha', 'multipleLocal'); ?>
                    <input type="password" name="confirm_new_pass" value="" />
                </label>
            </div>
            <div>
                <input type="submit" value="<?php \MapasCulturais\i::esc_attr_e('Guardar alteraçoes', 'multipleLocal'); ?>" />
            </div>
            <hr>
            <div>
                <?php if($config['strategies']['govbr']['visible']): ?>
                    <?php if($has_seal_govbr):?> 
                        <div class="gov-br-auth">
                            <img src="<?=$this->asset("img/govbr-auth.png", false)?>">
                        </div>
                    <?php else:?>
                        <div class="gov-br-sign-in">
                            <a href="http://localhost/autenticacao/govbr/" style="background-color: #eee;color: black;">
                                <span><?php \MapasCulturais\i::esc_attr_e('Vincular conta com', 'multipleLocal'); ?></span>
                                <img src="<?=$this->asset("img/sing-in-govbr.png", false)?>">
                            </a>
                        </div>
                    <?php endif ?>
                <?php endif ?>
            </div>
        </div>
    </form>
    <br>
</div>
