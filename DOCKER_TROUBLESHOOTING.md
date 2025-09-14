# Guia de Troubleshooting - Docker

## Problema Identificado

A aplicação está falhando ao conectar com o banco de dados MySQL no ambiente EasyPanel, apresentando o erro "Database connection failed: Invalid response".

## Análise dos Problemas Encontrados

### 1. **Configuração de Rede Docker**
O arquivo `docker-compose.yml` está configurado para desenvolvimento local, mas no EasyPanel a configuração de rede pode ser diferente.

### 2. **Timeout de Conexão Insuficiente**
O timeout original de 5 segundos pode ser insuficiente em ambientes de produção.

### 3. **Falta de Informações de Debug**
O script original não fornecia informações suficientes para diagnosticar problemas de conectividade.

### 4. **Tratamento de Banco Inexistente**
O script não verificava se o banco de dados existe antes de tentar conectar.

## Soluções Implementadas

### 1. **Script de Teste de Banco Melhorado** (`docker/test-db.php`)
- ✅ Informações detalhadas de debug
- ✅ Teste de conectividade em etapas
- ✅ Criação automática do banco se não existir
- ✅ Timeout aumentado para 10 segundos
- ✅ Melhor tratamento de erros

### 2. **Script de Inicialização Aprimorado** (`docker/start.sh`)
- ✅ Logs mais detalhados
- ✅ Relatório de erro completo
- ✅ Informações do ambiente para debug

### 3. **Configurações de Produção** (`.env.production`)
- ✅ Configurações específicas de charset e collation
- ✅ Timeout de conexão aumentado
- ✅ Modo strict habilitado

### 4. **Script de Diagnóstico** (`docker/diagnose.sh`)
- ✅ Diagnóstico completo do ambiente
- ✅ Teste de conectividade de rede
- ✅ Verificação de DNS
- ✅ Informações do sistema

## Como Usar as Melhorias

### Para Debug Imediato
```bash
# Execute o diagnóstico completo
docker exec -it <container_name> /var/www/html/docker/diagnose.sh

# Ou teste apenas a conexão do banco
docker exec -it <container_name> php /var/www/html/docker/test-db.php
```

### Configurações Recomendadas para EasyPanel

1. **Variáveis de Ambiente**
   ```env
   DB_HOST=<nome_do_servico_mysql_no_easypanel>
   DB_PORT=3306
   DB_DATABASE=closy
   DB_USERNAME=<usuario_mysql>
   DB_PASSWORD=<senha_mysql>
   DB_TIMEOUT=30
   ```

2. **Verificar Conectividade de Rede**
   - Certifique-se de que os containers estão na mesma rede
   - Verifique se o nome do host do banco está correto
   - Confirme se o serviço MySQL está rodando

## Possíveis Causas do Problema Original

### 1. **Nome do Host Incorreto**
- No EasyPanel, o nome do host pode ser diferente de `closy_database`
- Verifique o nome real do serviço MySQL no painel

### 2. **Rede Docker**
- Containers podem não estar na mesma rede
- Configuração de rede do EasyPanel pode ser diferente

### 3. **Timing de Inicialização**
- MySQL pode demorar mais para inicializar
- Timeout original muito baixo

### 4. **Credenciais**
- Usuário ou senha incorretos
- Banco de dados não existe

## Próximos Passos

1. **Rebuild da Imagem**
   ```bash
   docker build -t closy-app .
   ```

2. **Deploy no EasyPanel**
   - Use a nova imagem com as melhorias
   - Verifique as variáveis de ambiente
   - Execute o diagnóstico se houver problemas

3. **Monitoramento**
   - Acompanhe os logs detalhados
   - Use o script de diagnóstico para troubleshooting

## Comandos Úteis para Debug

```bash
# Ver logs do container
docker logs <container_name>

# Executar diagnóstico completo
docker exec -it <container_name> /var/www/html/docker/diagnose.sh

# Testar conexão manual
docker exec -it <container_name> php /var/www/html/docker/test-db.php

# Verificar variáveis de ambiente
docker exec -it <container_name> env | grep DB_

# Testar conectividade de rede
docker exec -it <container_name> nc -z <db_host> <db_port>
```

## Contato

Se o problema persistir após essas melhorias, execute o script de diagnóstico e compartilhe a saída completa para análise adicional.