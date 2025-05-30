## 1. Visão Geral

Este sistema consiste em uma API RESTful de gerenciamento de tarefas, construída para fornecer uma solução escalável e modular para gestão de usuários e atribuições de atividades. O objetivo principal é permitir a criação, leitura, atualização e remoção de entidades de Usuário e Tarefa, bem como autenticação segura via tokens (por exemplo, JWT), garantindo flexibilidade para integração com aplicações frontend ou microserviços de terceiros.

A solução foi concebida principalmente para equipes que precisam de um backend robusto para automação de fluxo de trabalho e acompanhamento de tarefas em tempo real. Utilizando Laravel 10 como framework backend, MySQL para persistência de dados e Docker para padronização de ambiente, o sistema oferece:

* **Endpoints para Usuários**: permitem criação, leitura, atualização e exclusão (soft delete) de usuários.
* **Endpoints para Tarefas**: viabilizam criação de tarefas associadas a usuários, consulta de tarefas individuais, listagem filtrada por usuário, atualização de atributos (título, descrição, status) e remoção de tarefas.
* **Autenticação via Token**: endpoints de login e logout para geração e invalidação de tokens, garantindo acesso controlado aos recursos.

Este projeto é ideal para cenários empresariais ou startups que buscam padronizar processos de desenvolvimento incremental, com foco em manutenibilidade, testabilidade e pronta escalabilidade.

## 2. Decisões Arquiteturais

Para maximizar a manutenibilidade e permitir evoluções futuras sem comprometer a integridade do código, adotamos o **Domain-Driven Design (DDD)** combinado com os princípios da **Clean Architecture**. As principais motivações e padrões aplicados são:

1. **Separação de Camadas (Onion/Clean Architecture)**

    * **Domain**: contém modelos de domínio puros (Entidades, Value Objects, Enums) e interfaces de repositórios, sem dependências de frameworks. Exemplo: `App\Domain\Tasks\Entities\Task`, `App\Domain\Users\Entities\User`, `App\Domain\Tasks\Enums\TaskStatus`.
    * **Application**: implementa casos de uso (Use Cases) e DTOs (Data Transfer Objects) para orquestrar a lógica de negócio. Exemplo: `App\Application\UseCases\Tasks\CreateTaskUseCase`, `App\Application\DTOs\CreateTaskDTO`.
    * **Infrastructure**: mapeamento para persistência e implementação concreta de repositórios (repositories), validação HTTP (Validators no Laravel) e configuração de dependências. Exemplo: repositórios que implementam `TaskRepositoryInterface` usando Eloquent.
    * **Presentation (Infrastructure\Http)**: controllers, rotas e camada HTTP, onde a biblioteca Laravel trata requisições e respostas. Exemplo: `App\Infrastructure\Http\Controllers\TaskController` e validadores como `CreateTaskValidator`.

   Essa divisão assegura que a lógica de domínio não dependa de frameworks específicos, facilitando testes unitários isolados e permitindo troca de tecnologias (por exemplo, migrar de MySQL para outro banco) minimamente impactante.

2. **Laravel 10 e Composer como Foundation**

    * Versão mais recente do framework Laravel, garantindo suporte a features modernas (PHP 8.1+, melhorias de performance e segurança).
    * Autoloader PSR-4 via Composer para carregamento de classes, organizando pacotes por namespace conforme camadas definidas.

3. **MySQL para Persistência Relacional**

    * Utilizamos MySQL pela estabilidade, escalabilidade horizontal e ampla adoção no mercado.
    * As entidades de domínio são mapeadas para tabelas (por exemplo, `users`, `tasks`), respeitando relações (foreign keys) e constraints, garantindo integridade referencial.

4. **Docker para Padronização de Ambiente**

    * Toda a aplicação roda em contêineres Docker (PHP-FPM, Nginx, MySQL), assegurando que desenvolvedores e pipeline CI/CD usem o mesmo ambiente isolado.
    * Simplifica a configuração de variáveis de ambiente, volume de dados para persistência local e redes internas.

5. **Testes Automatizados com PHPUnit**

    * A camada de testes unitários é focada em cada caso de uso (Use Cases) e, quando necessário, testes de integração para repositórios e controllers.
    * Métricas de cobertura de testes são estabelecidas para garantir que a lógica crítica do domínio permaneça validada a cada alteração de código.

6. **Documentação de API com Scribe**

    * A ferramenta Scribe ([https://scribe.readthedocs.io](https://scribe.readthedocs.io)) gera documentação Swagger-like a partir de anotações nos controllers, garantindo que endpoints (por exemplo, `POST /tasks`) fiquem documentados automaticamente.
    * Isso facilita a comunicação com times de frontend e terceiros, pois a documentação é sempre sincronizada com o código-fonte.

7. **Ferramentas de Teste de Requisição: Postman**

    * Coleções Postman são fornecidas para validação manual de endpoints, testes de fluxos de autenticação e simulações de uso.
    * Permite disparar requisições em diferentes ambientes (local, staging, produção) apenas alterando variáveis de ambiente.

8. **Padrões de Projeto e Boas Práticas**

    * **DTOs (Data Transfer Objects)**: garantem que dados de requisição sejam validados e convertidos antes de chegar ao domínio. Evita vazamento de objetos de framework dentro da lógica de negócio.
    * **Repositórios**: abstraem a persistência (Query Builders, Eloquent) da lógica de domínio, facilitando a troca de ORM ou a simulação de banco de dados em testes.
    * **Validators (FormRequest)**: utilizam regras de validação do Laravel para garantir integridade de dados antes de acionar os Use Cases.
    * **Enums**: uso de enums PHP (`TaskStatus`) para restringir valores de campos críticos (status da tarefa), aumentando a segurança em tempo de execução.
    * **Soft Deletes**: recomendação de uso em `DELETE /users/{id}` para manter histórico e viabilizar recuperação de registros.

Essa combinação de DDD, Clean Architecture e o ecossistema Laravel 10 garante que a evolução do projeto seja incremental, com baixo acoplamento entre camadas e alta coesão dentro de cada contexto de responsabilidade.

## 3. Modelagem de Dados

A modelagem de dados deste sistema é baseada em duas entidades principais: **users** e **tasks**. Abaixo, apresentamos o diagrama ER e a descrição detalhada de cada tabela.

### 3.1 Diagrama Entidade-Relacionamento (ER)

```mermaid
erDiagram
    USERS {
        BIGINT id PK
        VARCHAR name
        VARCHAR email
        VARCHAR cpf
        TIMESTAMP email_verified_at
        VARCHAR password
        ENUM role
        VARCHAR remember_token
        TIMESTAMP created_at
        TIMESTAMP updated_at
        TIMESTAMP deleted_at
    }
    TASKS {
        BIGINT id PK
        VARCHAR title
        TEXT description
        ENUM status
        BIGINT created_by FK
        BIGINT assigned_to FK
        TIMESTAMP deleted_at
        TIMESTAMP created_at
        TIMESTAMP updated_at
    }

    USERS ||--o{ TASKS : "created_by"
    USERS ||--o{ TASKS : "assigned_to"
```

*No diagrama acima:*

* A entidade **USERS** possui relacionamento de um-para-muitos com **TASKS** através de duas chaves estrangeiras: `created_by` e `assigned_to`.
* Cada registro em **TASKS** referencia um usuário que o criou (`created_by`) e outro (ou mesmo) usuário a quem a tarefa é atribuída (`assigned_to`).

### 3.2 Detalhamento das Tabelas

#### 3.2.1 Tabela `users`

| Coluna              | Tipo                   | Restrições                      | Descrição                                               |
| ------------------- | ---------------------- | ------------------------------- | ------------------------------------------------------- |
| `id`                | `BIGINT UNSIGNED`      | `PRIMARY KEY`, `AUTO_INCREMENT` | Identificador único do usuário.                         |
| `name`              | `VARCHAR(255)`         | `NOT NULL`                      | Nome completo do usuário.                               |
| `email`             | `VARCHAR(255)`         | `NOT NULL`, `UNIQUE`            | Endereço de e-mail, utilizado para login e comunicação. |
| `cpf`               | `VARCHAR(14)`          | `NOT NULL`, `UNIQUE`            | Documento de CPF formatado (ex.: `123.456.789-00`).     |
| `email_verified_at` | `TIMESTAMP`            | `NULLABLE`                      | Marca temporal de verificação de e-mail.                |
| `password`          | `VARCHAR(255)`         | `NOT NULL`                      | Senha criptografada.                                    |
| `role`              | `ENUM('admin','user')` | `NOT NULL`, `DEFAULT 'user'`    | Papel de permissão (administrador ou usuário comum).    |
| `remember_token`    | `VARCHAR(100)`         | `NULLABLE`                      | Token para lembrar sessão (lembrar-me).                 |
| `created_at`        | `TIMESTAMP`            | `NULLABLE`                      | Data e hora de criação do registro.                     |
| `updated_at`        | `TIMESTAMP`            | `NULLABLE`                      | Data e hora da última atualização do registro.          |
| `deleted_at`        | `TIMESTAMP`            | `NULLABLE`                      | Data e hora do soft delete (remoção lógica).            |

* **Observações**:

    * A coluna `deleted_at` habilita **Soft Deletes**, preservando histórico e permitindo restaurar registros apagados.
    * As restrições de `UNIQUE` em `email` e `cpf` garantem que não haja duplicidade de usuários por estes campos.

#### 3.2.2 Tabela `tasks`

| Coluna        | Tipo                                        | Restrições                                                    | Descrição                                                  |
| ------------- | ------------------------------------------- | ------------------------------------------------------------- | ---------------------------------------------------------- |
| `id`          | `BIGINT UNSIGNED`                           | `PRIMARY KEY`, `AUTO_INCREMENT`                               | Identificador único da tarefa.                             |
| `title`       | `VARCHAR(255)`                              | `NOT NULL`                                                    | Título resumido da tarefa.                                 |
| `description` | `TEXT`                                      | `NOT NULL`                                                    | Descrição completa e detalhada da tarefa.                  |
| `status`      | `ENUM('pending','in_progress','completed')` | `NOT NULL`, `DEFAULT 'pending'`                               | Status atual da tarefa.                                    |
| `created_by`  | `BIGINT UNSIGNED`                           | `NOT NULL`, `FOREIGN KEY` → `users(id)` (`ON DELETE CASCADE`) | Referência ao usuário que criou a tarefa.                  |
| `assigned_to` | `BIGINT UNSIGNED`                           | `NOT NULL`, `FOREIGN KEY` → `users(id)` (`ON DELETE CASCADE`) | Referência ao usuário responsável pela execução da tarefa. |
| `created_at`  | `TIMESTAMP`                                 | `NULLABLE`                                                    | Data e hora de criação da tarefa.                          |
| `updated_at`  | `TIMESTAMP`                                 | `NULLABLE`                                                    | Data e hora da última atualização da tarefa.               |
| `deleted_at`  | `TIMESTAMP`                                 | `NULLABLE`                                                    | Data e hora do soft delete (remoção lógica).               |

* **Relacionamentos**:

    * `created_by` ↔ `users.id`: Se o usuário for removido, todas as tarefas criadas por ele também são removidas via **ON DELETE CASCADE**.
    * `assigned_to` ↔ `users.id`: Se o usuário atribuído for removido, as tarefas também são removidas.

* **Status enum**: A coluna `status` restringe o valor a `pending`, `in_progress` ou `completed`, refletindo os possíveis estados de um ciclo de vida de tarefa.

### 3.3 Considerações Sobre a Modelagem

1. **Integridade Referencial**: As chaves estrangeiras em `tasks` garantem que não existam tarefas sem um usuário criador ou atribuído.
2. **Soft Deletes**: Ambas as tabelas utilizam `deleted_at` para remoção lógica, mantendo histórico e permitindo auditoria.
3. **Padronização de Timestamps**: Os campos `created_at` e `updated_at` facilitam rastreabilidade e auditoria de mudanças.
4. **Escalabilidade**: A estrutura simplificada de duas tabelas permite adicionar facilmente novos relacionamentos (por exemplo, comentários em tarefas, etiquetas, etc.) sem refatorar toda a modelagem.


## 4. Fluxo de Requisições

A seguir, descrevemos o fluxo de requisições principais para interação com a API, utilizando `{{baseUrl}}` (por exemplo, `http://localhost`) como URL base. Importante: após o passo de **Login**, todas as requisições subsequentes devem enviar o cabeçalho de autenticação:

```
Authorization: Bearer {token}
```

### 4.1 Criar um Usuário

* **Endpoint**: `POST {{baseUrl}}/api/users`

* **Descrição**: Cria um novo registro de usuário no sistema.

* **Request Body (JSON)**:

  ```json
  {
      "name": "Fernando Salatti",
      "email": "fernandosalatti@hotmail.com",
      "cpf": "123.456.289-32",
      "password": "senha123",
      "password_confirmation": "senha123"
  }
  ```

* **Exemplo de Resposta (201 Created)**:

  ```json
  {
      "id": 1,
      "name": "Fernando Salatti",
      "email": "fernandosalatti@hotmail.com",
      "cpf": "123.456.289-32",
      "created_at": "2024-05-20T00:00:00.000000Z",
      "updated_at": "2024-05-20T00:00:00.000000Z"
  }
  ```

* **Possíveis Erros**:

    * `422 Unprocessable Entity` caso falte algum campo obrigatório ou haja conflito de unicidade no e-mail/CPF.

### 4.2 Login

* **Endpoint**: `POST {{baseUrl}}/api/auth/login`

* **Descrição**: Autentica credenciais de usuário e retorna um token JWT válido por 1 dia.

* **Request Body (JSON)**:

  ```json
  {
    "email": "fernandosalatti@hotmail.com",
    "password": "senha123"
  }
  ```

* **Exemplo de Resposta (200 OK)**:

  ```json
  {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0OjgxL2FwaS9hdXRoL2xvZ2luIiwiaWF0IjoxNzQ4NzE3ODc0LCJleHAiOjE3NDg3MjE0NzQsIm5iZiI6MTc0ODcxNzg3NCwianRpIjoibWR6alZzM1l5Q0ZmM2dHMCIsInN1YiI6IjEiLCJwcnYiOiI2MmJmOTM4OTVjNDUxOGJlY2ZiMTY1MDM5M2UzZTE2ODNjMGZmOGI5In0.kAP4bt7S5juozyImT7jCbDG3CuwpOpwLQ6Y60o6y05g"
  }
  ```

* **Possíveis Erros**:

    * `401 Unauthorized` caso as credenciais estejam inválidas.
    * `422 Unprocessable Entity` caso o formato do JSON esteja incorreto.

> **Observação**: O token retornado deve ser incluído no header `Authorization` para todas as requisições seguintes.

### 4.3 Criar Tarefa

* **Endpoint**: `POST {{baseUrl}}/api/tasks`

* **Cabeçalhos**:

    * `Authorization: Bearer {token}`

* **Descrição**: Cria uma nova tarefa vinculada a um usuário existente.

* **Request Body (JSON)**:

  ```json
  {
      "title": "Implementar API",
      "description": "Finalizar módulo de tarefas",
      "status": "pending",
      "assigned_to": 1
  }
  ```

* **Exemplo de Resposta (201 Created)**:

  ```json
  {
      "id": 1,
      "title": "Implementar API",
      "description": "Finalizar módulo de tarefas",
      "status": "pending",
      "assigned_to": 1,
      "created_by": 1,
      "created_at": "2024-05-20T00:00:00.000000Z"
  }
  ```

* **Possíveis Erros**:

    * `400 Bad Request` em caso de exceções genéricas.
    * `404 Not Found` se o usuário especificado em `assigned_to` não for encontrado.
    * `422 Unprocessable Entity` se algum campo não satisfizer as regras de validação definidas no `CreateTaskValidator`.

### 4.4 Atualizar Tarefa

* **Endpoint**: `PUT {{baseUrl}}/api/tasks/{taskId}`

* **Cabeçalhos**:

    * `Authorization: Bearer {token}`

* **Descrição**: Atualiza campos de uma tarefa existente, podendo alterar título, descrição, status ou usuário atribuído.

* **Parâmetro de Rota**:

    * `taskId` (integer): ID da tarefa a ser atualizada.

* **Request Body (JSON)**:

  ```json
  {
      "title": "Tarefa Finalizada",
      "description": "Concluída com sucesso!",
      "status": "completed",
      "assigned_to": 2
  }
  ```

* **Exemplo de Resposta (200 OK)**:

  ```json
  {
      "id": 1,
      "title": "Tarefa Finalizada",
      "description": "Concluída com sucesso!",
      "status": "completed",
      "assigned_to": 2,
      "created_by": 1,
      "updated_at": "2024-05-21T12:34:56.000000Z"
  }
  ```

* **Possíveis Erros**:

    * `400 Bad Request` para exceções genéricas.
    * `404 Not Found` se `taskId` ou `assigned_to` não existirem.
    * `422 Unprocessable Entity` se violar regras de validação (por exemplo, status inválido).

### 4.5 Logout

* **Endpoint**: `POST {{baseUrl}}/api/auth/logout`

* **Cabeçalhos**:

    * `Authorization: Bearer {token}`

* **Descrição**: Invalida o token JWT atual, encerrando a sessão do usuário.

* **Request Body**: *Não aplica (nenhum parâmetro requerido no body)*

* **Exemplo de Resposta (204 No Content)**:

    * Retorna apenas o status HTTP `204 No Content` para indicar logout bem-sucedido.

* **Possíveis Erros**:

    * `401 Unauthorized` caso o token não seja válido ou já tenha expirado.

> **Nota**: Para recuperar o arquivo completo de requisições e variáveis de ambiente, importe a coleção Postman disponibilizada na raiz do projeto. O fluxo acima segue essa mesma ordem e formato, utilizando `localhost` como `{{baseUrl}}`.
