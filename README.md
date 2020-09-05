# RestApiDemo
Demo Rest Api
# Установка
На сервере должны быть установлены PHP 7.1+, MySql 5.7+, Composer    
Клонируем репозиторий, в корневой директории:    
#> composer install    
#> php bin/console demo:install    
Команда создает базу данных, таблицы и заполняет их первоначальными данными.    
# Использование
Для проверки запроса можно использовать [Postman](https://www.postman.com/)    
## Endpoint:
POST /api/v2/reservation    
### Параметры запроса:    
date – дата желаемого бронирования – обязательное поле, пример: '01.10.2020'    
from – время начала бронирования – обязательное поле, пример: '10:00'    
to – время окончания бронирования – обязательное поле, пример: '10:59'    
table_id – идентификатор бронируемого стола – необязательное поле    
