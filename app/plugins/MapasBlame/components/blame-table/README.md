# Componente `<blame-table>`
Componente para listagem dos logs de acesso do plugin MapasBlame.
  
## Propriedades
- *Number **userId** opcional* - ID do usuário

### Importando componente
```PHP
<?php 
$this->import('blame-table');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<blame-table></blame-table>

<!-- utilizaçao para listagem dos logs do usuário -->
<blame-table :user-id="8"></blame-table>
```