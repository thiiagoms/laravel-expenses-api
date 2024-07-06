# Expenses APi Management

## Dependencies

- Docker :whale:

## Install

1. Clone the repository and go to `expenses` directory:

```shell
git clone https://github.com/thiiagoms/expenses
cd expenses
```

2. Setup containers and install dependencies inside app container:

```shell
docker-compose up -d
docker-compose exec app bash
thiiagoms@e795fc12781f:/var/www$ composer install
```

3. Copy `.env.example` to `.env` and generate application key:

```shell
thiiagoms@e795fc12781f:/var/www$ cp .env.example .env
thiiagoms@e795fc12781f:/var/www$ php artisan key:generate
```

4. Run migrations and setup queue:

```shell
thiiagoms@e795fc12781f:/var/www$ php artisan migrate
thiiagoms@e795fc12781f:/var/www$ php artisan queue:work
```

5. Generate swagger docs:

```shell
thiiagoms@e795fc12781f:/var/www$ php artisan l5-swagger:generate
```

6. Go to:

- `http://localhost:8000/api/documentation` - to see swagger docs

- `http://localhost:8000/api` - to use application

## Tests and list

1. To run tests and lint:

```shell
thiiagoms@e795fc12781f:/var/www$ php artisan tests
thiiagoms@e795fc12781f:/var/www$ composer pint
