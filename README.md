# CloudMoura

Aplicação web desenvolvida em PHP utilizando Docker.

## Estrutura do Projeto

```
src/
├── public/           # Arquivos públicos e assets
│   ├── css/
│   ├── js/
│   ├── img/
│   ├── webfonts/
│   ├── bootstrap.php # Arquivo de inicialização
│   └── index.php    # Front Controller
├── pages/           # Páginas da aplicação
│   ├── admin/
│   ├── user/
│   └── auth/
├── Api/            # Endpoints da API
├── Config/         # Configurações
│   └── Definitions.php
├── Includes/       # Funções helpers e utilitários
│   ├── Functions.php
│   └── CheckSession.php
└── vendor/         # Dependências (via Composer)

.infra/            # Configurações do ambiente
├── nginx/
└── php/
```

## Fluxo das Requisições

1. Todas as requisições entram por `src/public/index.php`
2. O `bootstrap.php` é carregado e inicializa:
   - Funções utilitárias (`Functions.php`)
   - Definições globais (`Definitions.php`)
   - Autoloader do Composer
   - Variáveis de ambiente
   - API handlers
   - Verificação de sessão (`CheckSession.php`)
3. Com base na URI, o conteúdo apropriado é carregado de `src/pages/`

## Ambiente de Desenvolvimento

### Pré-requisitos
- Docker
- Docker Compose

### Configuração
1. Clone o repositório
2. Execute `docker-compose up -d`
3. Instale as dependências: `docker-compose run composer install`
4. Você executar o script `src/scripts/build.sh`

### Containers
- PHP 8.1 (FPM) (cloudmoura-php)
- Nginx (cloudmoura-nginx)
- Composer (cloudmoura-composer)

## Rede
O projeto utiliza a rede `npm-network` para integração com outros serviços.

## 🚀 Características

- Sistema de autenticação seguro
- Gerenciamento de usuários
- Interface moderna e responsiva
- API RESTful
- Banco de dados SQLite
- Sistema de logs para debug
- Página de manutenção
- Sistema de roles (admin/user)

## 📋 Pré-requisitos

- PHP 8.0 ou superior
- SQLite3
- Nginx
- Composer (para gerenciamento de dependências)

## 🔧 Instalação

1. Clone o repositório:
```bash
git clone https://github.com/adrianodemoura/CloudMoura.git
```
```bash
cd CloudMoura
```

2. Docker
```bash
cp docker-compose-default.yml docker-compose.yml
```
```bash
docker-compose up -d
```

3. Acesse o sistema através do navegador:
```
http://localhost/
```

4. Na primeira vez que acessar, faça o login com as credenciais padrão (admin@admin.com / Admin01). O sistema irá automaticamente:
   - Criar o banco de dados SQLite
   - Criar as tabelas necessárias
   - Configurar o primeiro usuário administrador

## 📁 Estrutura do Projeto

```
src/
├── api/             # Endpoints da API REST
├── config/          # Arquivos de configuração
├── data/            # Diretório para dados do SQLite
├── includes/        # Classes e funções auxiliares
├── logs/            # Diretório de logs
├── public/          # Arquivos públicos e ponto de entrada
├── scripts/         # Scripts utilitários
└── uploads/         # Diretório para guardar seus arquivos
```

## 🔐 Credenciais Padrão

- **Email**: admin@admin.com
- **Senha**: Admin01

## 🛠️ Tecnologias Utilizadas

- PHP 8.0+
- SQLite3
- HTML5
- CSS3
- JavaScript
- Bootstrap 5

## 📝 Logs

O sistema mantém logs detalhados para debug e monitoramento. Os logs são armazenados em:
```
src/logs/
```

## 📤 Uploads
O sistema mantém seus arquivos pessoais em:
```
src/uploads/
```

## 🔒 Segurança

- Senhas armazenadas com hash seguro
- Proteção contra SQL Injection
- Validação de entrada de dados
- Sistema de roles para controle de acesso
- Página de manutenção para downtime planejado

## 🤝 Contribuindo

1. Faça um Fork do projeto
2. Crie uma Branch para sua Feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a Branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## 📄 Licença

Este projeto está sob a licença MIT. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.

## ✨ Agradecimentos

- Equipe de desenvolvimento
- Contribuidores
- Comunidade open source

## 📞 Suporte

Para suporte, envie um email para seu-email@dominio.com ou abra uma issue no GitHub. 


## 📸 Screenshots

![Screenshot 1](src/public/img/screnshot_001.png)
![Screenshot 2](src/public/img/screnshot_002.png)

## Vídeos

### Instalação
![Instalação do CloudMoura](src/public//videos/instalação-CloudMoura.gif)