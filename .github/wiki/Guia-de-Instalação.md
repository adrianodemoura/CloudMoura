# Guia de Instalação

Este guia fornece instruções detalhadas para instalar e configurar o CloudMoura em seu servidor.

## 📋 Pré-requisitos

- PHP 8.0 ou superior
- SQLite3
- Apache/Nginx
- Composer

## 🔧 Passo a Passo

### 1. Clone do Repositório

```bash
git clone https://github.com/seu-usuario/cloudmoura.git
cd cloudmoura
```

### 2. Configuração do Servidor Web

#### Apache
```apache
<VirtualHost *:80>
    ServerName seu-dominio.com
    DocumentRoot /caminho/para/cloudmoura/src/public
    
    <Directory /caminho/para/cloudmoura/src/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### Nginx
```nginx
server {
    listen 80;
    server_name seu-dominio.com;
    root /caminho/para/cloudmoura/src/public;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
}
```

### 3. Inicialização do Sistema

1. Acesse o sistema através do navegador: `http://seu-dominio.com`
2. Tente fazer login três vezes com as credenciais padrão:
   - Email: admin@admin.com
   - Senha: Admin01
3. O sistema irá automaticamente:
   - Criar o banco de dados SQLite
   - Configurar as tabelas necessárias
   - Criar o usuário administrador

### 4. Verificação da Instalação

Após a instalação, verifique se:
- O sistema está acessível via navegador
- Você consegue fazer login com as credenciais padrão
- O upload de arquivos está funcionando
- Os logs estão sendo gerados corretamente

## 🔍 Solução de Problemas

### Problemas Comuns

1. **Erro 500**
   - Verifique as permissões dos diretórios
   - Confira os logs do PHP e do servidor web

2. **Erro de Conexão com Banco de Dados**
   - Verifique se o SQLite3 está instalado
   - Confira as permissões do diretório `src/data`

3. **Problemas de Upload**
   - Verifique as configurações do PHP (upload_max_filesize, post_max_size)
   - Confira as permissões do diretório `src/uploads`

## 📚 Links Úteis

- [Documentação do PHP](https://www.php.net/docs.php)
- [Documentação do SQLite](https://www.sqlite.org/docs.html)
- [Documentação do Apache](https://httpd.apache.org/docs/)
- [Documentação do Nginx](https://nginx.org/en/docs/) 