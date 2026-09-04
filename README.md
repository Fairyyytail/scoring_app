# scoring_app

## Установка


* Скопировать переменные окружения
```
cp .env.dev .env
```

# Создать образ
```
make init
```
# После успешного создания образа приложение будет доступно на порту
```
http://localhost:8337/
```
База данных `scoring`, миграции и наполнение БД данными выполнятся автоматический

# Запуск тестов осуществляется командой
```
make test
```
# Консольная команда по заданию
```
docker compose exec scoring_app bin/console app:scoring:calculate - для расчёта по всем
docker compose exec scoring_app bin/console app:scoring:calculate uuid - для расчёта по одному
```

# Краткое пояснение
``` 
На проекте слоистая архитектура, пусть названия директорий (Handler, Presentation, Infrastructure) не смущает - это не DDD, CQRS.
ValueObject, NamedConstructor, One UseCase - One Class(Handler), RepositoryIntrafaces и т.д - просто принципы объектного дизайна
и SOLID, предшествующие появлению DDD. Сходство только по названиям.
```




Спасибо за внимание!