#!/bin/bash

# Script de diagnóstico para problemas de conectividade Docker
# Execute este script para obter informações detalhadas sobre o ambiente

echo "=== DIAGNÓSTICO DO AMBIENTE DOCKER ==="
echo "Data/Hora: $(date)"
echo "Hostname: $(hostname)"
echo "Container IP: $(hostname -i 2>/dev/null || echo 'Não disponível')"
echo

echo "=== VARIÁVEIS DE AMBIENTE DO BANCO ==="
echo "DB_HOST: ${DB_HOST:-'NÃO DEFINIDO'}"
echo "DB_PORT: ${DB_PORT:-'NÃO DEFINIDO'}"
echo "DB_DATABASE: ${DB_DATABASE:-'NÃO DEFINIDO'}"
echo "DB_USERNAME: ${DB_USERNAME:-'NÃO DEFINIDO'}"
echo "DB_PASSWORD: $([ -n "$DB_PASSWORD" ] && echo '[DEFINIDO]' || echo '[NÃO DEFINIDO]')"
echo

echo "=== TESTE DE CONECTIVIDADE DE REDE ==="
if command -v nc >/dev/null 2>&1; then
    echo "Testando conectividade TCP para ${DB_HOST}:${DB_PORT}..."
    if nc -z "${DB_HOST}" "${DB_PORT}" 2>/dev/null; then
        echo "✓ Conectividade TCP: SUCESSO"
    else
        echo "✗ Conectividade TCP: FALHOU"
    fi
else
    echo "netcat não disponível para teste de conectividade"
fi
echo

echo "=== RESOLUÇÃO DNS ==="
if command -v nslookup >/dev/null 2>&1; then
    echo "Resolvendo ${DB_HOST}..."
    nslookup "${DB_HOST}" 2>/dev/null || echo "Falha na resolução DNS"
elif command -v dig >/dev/null 2>&1; then
    echo "Resolvendo ${DB_HOST}..."
    dig "${DB_HOST}" +short 2>/dev/null || echo "Falha na resolução DNS"
else
    echo "Ferramentas DNS não disponíveis"
fi
echo

echo "=== INFORMAÇÕES DO PHP ==="
echo "Versão do PHP: $(php -v | head -n1)"
echo "Extensões PDO carregadas:"
php -m | grep -i pdo || echo "Nenhuma extensão PDO encontrada"
echo

echo "=== TESTE DE CONEXÃO COM BANCO ==="
echo "Executando teste detalhado de conexão..."
php /var/www/html/docker/test-db.php
echo

echo "=== INFORMAÇÕES DO SISTEMA ==="
echo "Distribuição: $(cat /etc/os-release | grep PRETTY_NAME | cut -d'=' -f2 | tr -d '"')"
echo "Arquitetura: $(uname -m)"
echo "Kernel: $(uname -r)"
echo "Uptime: $(uptime)"
echo

echo "=== PROCESSOS EM EXECUÇÃO ==="
echo "Processos relacionados ao MySQL/MariaDB:"
ps aux | grep -i mysql | grep -v grep || echo "Nenhum processo MySQL encontrado"
echo
echo "Processos PHP:"
ps aux | grep -i php | grep -v grep || echo "Nenhum processo PHP encontrado"
echo

echo "=== PORTAS EM USO ==="
if command -v netstat >/dev/null 2>&1; then
    echo "Portas TCP em escuta:"
    netstat -tlnp 2>/dev/null | head -10
elif command -v ss >/dev/null 2>&1; then
    echo "Portas TCP em escuta:"
    ss -tlnp | head -10
else
    echo "Ferramentas de rede não disponíveis"
fi
echo

echo "=== LOGS RECENTES ==="
echo "Últimas 10 linhas do log do supervisor:"
tail -10 /var/log/supervisor/supervisord.log 2>/dev/null || echo "Log do supervisor não encontrado"
echo

echo "=== FIM DO DIAGNÓSTICO ==="
echo "Para mais informações, verifique os logs em /var/log/"