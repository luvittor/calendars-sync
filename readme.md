# Microsoft API Calendar Integration

Este projeto fornece exemplos de scripts em PHP para interagir com a API de Calendário da Microsoft.

Os scripts permitem listar calendários e manipular eventos (listar, criar, atualizar e deletar), além de gerar e renovar tokens de autenticação.

## Configuração Inicial

### 1. Configurar o Azure e Obter Parâmetros

1. Acesse o [Azure Portal](https://portal.azure.com/) e faça login com suas credenciais.

2. Navegue até **Azure Active Directory** > **App registrations** e clique em **New registration** para registrar uma nova aplicação.

3. Preencha as informações necessárias:
   - **Name**: Nome da sua aplicação.
   - **Supported account types**: Escolha quem pode usar esta aplicação (geralmente "Accounts in this organizational directory only").
   - **Redirect URI**: Pode ser deixado em branco para este projeto.

4. Clique em **Register**.

5. Após o registro, você será redirecionado para a página da aplicação onde poderá obter os seguintes parâmetros:
   - **Application (client) ID**: Este será o seu `CLIENT_ID`.
   - **Directory (tenant) ID**: Este será o seu `TENANT_ID`.

6. Para gerar um `CLIENT_SECRET`, navegue até **Certificates & secrets** > **Client secrets** > **New client secret**. Copie o valor gerado e armazene como `CLIENT_SECRET` no arquivo `.env`.

7. Habilite a opção **Allow public client flows**:
   - Vá até **Authentication**.
   - Em **Allow public client flows**, marque **Yes**.

### 2. Definir Parâmetros

1. **Copie o arquivo `.env-example` para `.env`**:
   
   ```bash
   cp .env-example .env
   ```
   
   Em seguida, edite o arquivo `.env` e preencha os parâmetros conforme necessário.

2. **Parâmetros Necessários**:
   
   - **CLIENT_ID**: 
     - O ID do Cliente da sua aplicação registrada no [Azure](https://portal.azure.com/).
     - **Como obter**: No portal do [Azure](https://portal.azure.com/), acesse **Azure Active Directory** > **App registrations** > selecione sua aplicação > copie o "Application (client) ID".

   - **CLIENT_SECRET**:
     - O segredo do cliente para autenticação.
     - **Como obter**: No portal do [Azure](https://portal.azure.com/), acesse **Azure Active Directory** > **App registrations** > selecione sua aplicação > **Certificates & secrets** > **Client secrets** > **New client secret**. Copie o valor gerado.
     - **Nota**: Não necessário se quiser autenticação via certificado digital.

   - **CLIENT_SECRET_AUTH**:
     - Define o método de autenticação.
     - **TRUE**: Para autenticação usando `client_secret`.
     - **FALSE**: Para autenticação usando `client_assertion` (certificado digital).

   - **TENANT_ID**:
     - O ID do locatário (tenant) da sua organização no [Azure](https://portal.azure.com/).
     - **Como obter**: No portal do [Azure](https://portal.azure.com/), acesse **Azure Active Directory** > copie o "Tenant ID".

   - **SCOPES**:
     - As permissões necessárias para acessar o calendário.
     - **Valor padrão**: `openid profile Calendars.Read Calendars.ReadWrite`.
     - **Nota**: Não precisa ser alterado.

   - **CALENDAR_ID**:
     - O ID do calendário onde os eventos serão criados.
     - **Nota**: Deixe em branco para usar o calendário padrão. Caso deseje usar um calendário específico, rode `ms-calendar-list-all.php` após autenticação para recuperar o ID.

3. **Autenticação com `client_assertion` (certificado digital)**:
   
   Caso opte por usar `client_assertion`, será necessário gerar um certificado digital.

   - **Geração do Certificado**:
   
     No WSL ou em um ambiente Unix-like, rode:

     ```bash
     openssl req -newkey rsa:2048 -nodes -keyout private.pem -x509 -days 365 -out public.pem
     ```

     Isso gerará dois arquivos: `private.pem` (chave privada) e `public.pem` (certificado público).

   - **Suba o Certificado no Azure**:
     
     No portal do [Azure](https://portal.azure.com/), acesse **Azure Active Directory** > **App registrations** > selecione sua aplicação > **Certificates & secrets** > **Certificates** > **Upload certificate**. Suba o `public.pem`.

   - **Configuração dos Arquivos**:
     
     Mova os arquivos `private.pem` e `public.pem` para o diretório `ms-auth-cert` dentro do projeto.

### 3. Instalar Dependências

Instale as dependências do projeto utilizando o Composer:

```bash
composer install
```

### 4. Autenticação

Antes de usar os exemplos, você precisa gerar um token de acesso.

- **Usando `client_secret`**:

  Se o `CLIENT_SECRET_AUTH` estiver definido como `TRUE` no arquivo `.env`, use o seguinte comando para autenticar:

  ```bash
  php ms-auth-client-secret.php
  ```

  O token gerado será salvo no arquivo `ms-auth-client-secret-token.json`.

- **Usando `client_assertion`**:

  Se o `CLIENT_SECRET_AUTH` estiver definido como `FALSE`, use:

  ```bash
  php ms-auth-client-assertion.php
  ```

  O token gerado será salvo no arquivo `ms-auth-client-assertion-token.json`.

## Exemplos de Uso

### 1. Listar Todos os Calendários

Este script lista todos os calendários disponíveis na conta.

```bash
php ms-calendar-list-all.php
```

A lista de calendários será salva em `ms-calendar-list-all.json`.

### 2. Criar um Evento

Cria um novo evento no calendário especificado (ou no calendário padrão se `CALENDAR_ID` não estiver configurado).

```bash
php ms-calendar-event-create.php
```

Os detalhes do evento criado serão salvos em `ms-calendar-event-create.json`.

### 3. Atualizar um Evento

Atualiza um evento existente com base nas informações armazenadas em `ms-calendar-event-create.json`.

```bash
php ms-calendar-event-update.php
```

Os detalhes atualizados serão salvos no mesmo arquivo JSON.

### 4. Deletar um Evento

Deleta um evento existente com base nas informações armazenadas em `ms-calendar-event-create.json`.

```bash
php ms-calendar-event-delete.php
```

O evento será deletado e o arquivo JSON será removido.

### 5. Listar Todos os Eventos

Este script lista todos os eventos do calendário especificado (ou do calendário padrão) e salva em um arquivo JSON.

```bash
php ms-calendar-event-list-all.php
```

Os eventos serão salvos em `ms-calendar-event-list-all.json`.

### 6. Listar Eventos a partir de uma Data Específica

Lista eventos agendados a partir de uma data específica.

```bash
php ms-calendar-event-list-start-date.php
```

Os eventos serão exibidos na tela.

### 7. Listar Eventos Modificados Após uma Data

Lista eventos que foram modificados após uma data específica.

```bash
php ms-calendar-event-list-modified-after.php
```

Os eventos serão exibidos na tela.

## Considerações Finais

Esses scripts oferecem um ponto de partida para a integração com a API do Calendário da Microsoft. Eles podem ser adaptados para atender a necessidades específicas.
