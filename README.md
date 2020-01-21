##Установка

1. Склонировать репозиторий.

2. Инициализировать git подмодули:

   > git submodule init

3. Подгрузить подмодули:

    > git submodule update

4. Если используется ОС `Windows`,
то для корректной работы `percona` необходимо скопировать
содержимое директории
    > ./docker/laradock_example/percona
    
    в директорию
    > ./docker/laradock/percona
    
    Аналогично для `mysql`.

5. Скопировать файл

    > ./docker/laradock_example/.env
    
    в директорию
      > ./docker/laradock/

6. Скопировать содержимое директории

   > ./docker/laradock_example/nginx

    в директорию
      
   > ./docker/laradock/nginx
   
7. Если требуется, то настройте конфигурационный файл NGINX

    > ./docker/laradock/nginx/sites/project.conf