# Componente `<create-account>`
Componente para cadastrar uma conta no mapas culturais BaseV2
  
## Propriedades
- *String **config*** - json com configurações necessárias para o funcionamento da criação de contas (definidas no php)

### Importando componente
```PHP
<?php 
$this->import('create-account');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<create-account config='<?= $configs; ?>'></create-account>
```