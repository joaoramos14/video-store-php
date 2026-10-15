# 7 Rent — locadora de filmes

Sistema de locadora feito com Laravel 13 e MySQL. O cliente reserva o filme pelo site, retira na loja e devolve no balcão. O cadastro de filmes e gêneros usa a API do [TMDB](https://www.themoviedb.org/).

## Como funciona o aluguel

1. **Reserva (site):** o cliente escolhe o filme e os dias. A reserva já separa uma cópia do estoque.
2. **Retirada (balcão):** o admin confirma a retirada. O prazo de devolução só começa a contar aqui.
3. **Devolução (balcão):** o admin registra a devolução e a cópia volta ao estoque.

Desvios: o cliente pode **cancelar** a reserva antes de retirar, e a reserva **expira** se ninguém retirar no prazo (24 horas por padrão, configurável em `RENTAL_RESERVATION_HOURS`). Nos dois casos a cópia volta ao estoque.

**Multa por atraso:** cada dia (ou fração) de atraso custa 1,5 × a diária paga no aluguel.

O cliente só pode avaliar filmes que já retirou, uma avaliação por aluguel.

## Requisitos

- Docker e Docker Compose

## Como rodar

```bash
cp .env.example .env
docker compose up -d --build
```

Na primeira vez, o container `app` instala as dependências do Composer ao subir. Espere o `vendor/` aparecer (acompanhe com `docker compose logs -f app`) e rode:

```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

Abra http://localhost:8000. O navegador vai pedir e-mail e senha (autenticação HTTP Basic).

| Perfil  | E-mail                  | Senha  |
|---------|-------------------------|--------|
| Admin   | admin@locadora.test     | 123456 |
| Cliente | cliente@locadora.test   | 123456 |

Essas contas são só para desenvolvimento. Troque as senhas antes de publicar o sistema em qualquer lugar.

## Serviços do Docker

| Serviço     | Para que serve                                                   | Porta |
|-------------|------------------------------------------------------------------|-------|
| `app`       | Laravel (`php artisan serve`)                                    | 8000  |
| `scheduler` | Tarefas agendadas: expira reservas (a cada 5 min) e marca atrasos | —     |
| `vite`      | Assets do front-end                                              | 5173  |
| `db`        | MySQL 8                                                          | 3306  |

## TMDB

Para buscar filmes e gêneros no cadastro, crie uma chave gratuita em https://www.themoviedb.org/settings/api e coloque no `.env`:

```
TMDB_API_KEY=sua-chave
```

Se o certificado da rede bloquear a conexão SSL, use `TMDB_VERIFY_SSL=false`, só em desenvolvimento. Sem rede ou sem chave, as telas continuam abrindo, só sem as sugestões do TMDB.

## Testes

```bash
docker compose exec app php artisan test
```

Os testes usam SQLite em memória e não mexem no banco do Docker.
