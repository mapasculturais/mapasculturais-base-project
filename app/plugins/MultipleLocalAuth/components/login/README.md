# Componente `<login>`
Componente para efetuar login seguro no mapas culturais BaseV2
  
## Propriedades
- *String **config*** - json com configurações necessárias para o funcionamento do login (definidas no php)

### Importando componente
```PHP
<?php 
$this->import('login');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<login config='<?= $configs; ?>'></login>
```