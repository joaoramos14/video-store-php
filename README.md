# 7 Rent — locadora de filmes

Sistema de locadora desenvolvido com Laravel 13 e MySQL. O cliente reserva filmes pelo site, retira na loja e devolve no balcão. O cadastro de filmes e gêneros conta com integração à API do [TMDB](https://www.themoviedb.org/).

> Projeto de treinamento para praticar Laravel, Docker e integração com APIs.

## Como funciona o aluguel

1. **Reserva (site):** o cliente escolhe o filme e o período do aluguel. A reserva já separa uma cópia do estoque.
2. **Retirada (balcão):** o administrador confirma a retirada. O prazo de devolução começa a contar a partir desse momento.
3. **Devolução (balcão):** o administrador registra a devolução e a cópia volta a ficar disponível no estoque.

O cliente pode cancelar a reserva antes da retirada. Caso ninguém retire o filme dentro do prazo, a reserva expira automaticamente. O prazo padrão é de 24 horas e pode ser configurado pela variável `RENTAL_RESERVATION_HOURS`. Nos dois casos, a cópia retorna ao estoque.

**Multa por atraso:** cada dia ou fração de dia de atraso custa 1,5 vez o valor da diária do aluguel.

O cliente só pode avaliar filmes que já retirou e pode registrar uma avaliação por aluguel.

## Requisitos

- Docker
- Docker Compose
- Chave de API do TMDB para habilitar as sugestões de filmes e gêneros

## Como executar o projeto

Clone o repositório e acesse a pasta do projeto. Em seguida, crie o arquivo de ambiente:

```bash
cp .env.example .env
```

Abra o arquivo `.env` e preencha `DB_PASSWORD` (senha do usuário da aplicação no MySQL) e `DB_ROOT_PASSWORD` (senha do `root`). Use valores diferentes. O Docker Compose não sobe se uma das duas estiver vazia.

Se estiver reutilizando um volume de banco já inicializado, mantenha as credenciais consistentes com as que foram configuradas anteriormente. Alterar as variáveis no `.env` não altera automaticamente as senhas de um banco existente.

Para habilitar as sugestões de filmes e gêneros, configure também `TMDB_API_KEY` com sua chave de API do TMDB. Essa chave é opcional para iniciar a aplicação, mas necessária para utilizar a integração.

Inicie os serviços:

```bash
docker compose up -d --build
```

Na primeira execução, aguarde a instalação das dependências do Composer no container `app`. Para acompanhar os logs:

```bash
docker compose logs -f app
```

Quando a aplicação estiver pronta, gere a chave do Laravel e execute as migrations com os dados iniciais:

```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

Acesse [http://localhost:8000](http://localhost:8000).

O navegador solicitará e-mail e senha (autenticação HTTP Basic). O seed cria duas contas:

| Perfil  | E-mail                | Senha  |
|---------|-----------------------|--------|
| Admin   | admin@locadora.test   | 123456 |
| Cliente | cliente@locadora.test | 123456 |

Essas contas servem apenas para desenvolvimento. Troque as senhas antes de publicar o sistema em qualquer lugar.

## Serviços do Docker

| Serviço     | Para que serve                                                      | Porta                |
|-------------|---------------------------------------------------------------------|----------------------|
| `app`       | Laravel (`php artisan serve`)                                       | 8000                 |
| `scheduler` | Tarefas agendadas: expira reservas (a cada 5 min)                   | —                    |
| `vite`      | Assets do front-end                                                 | 5173                 |
| `db`        | MySQL 8                                                             | 3306 (só localhost)  |

## TMDB

Crie uma chave gratuita em https://www.themoviedb.org/settings/api e coloque no `.env`:

```
TMDB_API_KEY=sua-chave
```

Todo gênero é cadastrado com o ID do TMDB, para evitar duplicatas. Ao cadastrar um filme pela busca, se o gênero principal dele ainda não existir, a tela oferece adicioná-lo na hora.

Se o certificado da rede bloquear a conexão SSL, use `TMDB_VERIFY_SSL=false`, apenas em desenvolvimento.

## Testes

```bash
docker compose exec app php artisan test
```

Os testes usam SQLite em memória e não alteram o banco do Docker.