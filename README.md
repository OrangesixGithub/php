<div align="center">
  <h1>@Orangesix/PHP</h1>
  <p>Uma biblioteca moderna de funcionalidades php integrada ao framework Laravel.</p>
</div>
<img src="https://img.shields.io/static/v1?label=License&message=MIT&color=success"/>
<img src="https://img.shields.io/static/v1?label=CORE&message=PHP&color=blue&logo=php"/>
<img src="https://img.shields.io/static/v1?label=Framework&message=Lavarel&color=blue&logo=laravel"/>

## 📂 Estrutura de Diretórios

### 1. 🛡️ Acl (Access Control List)

Módulo responsável pelo gerenciamento de permissões e perfis de usuário. O sistema suporta configuração para ambientes
multi-filiais.

### 2. 🎮 Controller

Contém as classes base para os controladores da aplicação. `ControllerBase.php` Controlador abstrato que estende o
controller padrão do Laravel, fornecendo métodos comuns e padronização de respostas.

### 3. 🧮 Enum

Diretório reservado para Enumerações (Enums) gerais do sistema, facilitando a tipagem forte e a organização de
constantes.

### 4. ⚠️ Exceptions

Classes personalizadas de exceção para tratamento padronizado de erros.

- `Api.php` Exceções específicas para respostas de API.
- `Field.php` Exceções relacionadas a validação de campos.
- `Message.php` Exceções genéricas de mensagens do sistema.

### 5. 🔧 Function

Funções auxiliares ou helpers globais que podem ser utilizados em todo o sistema.

### 6. 🌐 HTTP

Camada HTTP adicional do pacote.

- `Resource` API Resources para transformação de dados antes de enviá-los como resposta JSON.

### 7. 🗃️ Models

Modelos base e centrais do sistema.

- `Core` Contém modelos abstratos ou traits que são compartilhados entre múltiplos modelos da aplicação, garantindo
  consistência no Eloquent.

### 8. 🏭 Repository

Implementação do padrão Repository para abstração da camada de dados.

- `Contract`: Interfaces que definem os contratos dos repositórios.
- `Core`: Lógica central dos repositórios.
- `Utils`: Utilitários para consultas e manipulação de dados.
- `RepositoryBase.php`: Classe base abstrata que implementa operações comuns de CRUD (Create, Read, Update, Delete).
- `DefaultRepository.php`: Implementação padrão para uso rápido.

### 9. 💼 Service

Camada de serviços para encapsular a regra de negócio.

- `Contract`: Interfaces para os serviços.
- `Core`: Lógica central dos serviços.
- `Response`: Classes para padronização de objetos de resposta de serviço (DTOs).
- `ServiceBase.php`: Classe base abstrata para todos os serviços.
- `DefaultService.php`: Implementação de serviço padrão.