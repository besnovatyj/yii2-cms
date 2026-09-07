# Manticore Search — демон полнотекстового поиска для сквозного поиска по сайту.
#
# Версия зафиксирована намеренно: морфология «запекается» в индекс, и смена версии демона —
# это то, что делают осознанно, с последующей переиндексацией (`make search-reindex`).
# Начиная с версии 25.0 языковые пакеты (в том числе русский лемматизатор) входят в образ,
# ставить их отдельно не нужно; встроенная авторизация появилась в 27.1.5.
#
# Своего manticore.conf здесь нет намеренно: образ собирает конфигурацию сам из переменных
# окружения с префиксами `searchd_` и `common_` (см. compose/storage.yml), а подменять готовый
# файл значило бы взять на себя ещё и пути к журналам, pid-файлу и каталогу данных.
FROM manticoresearch/manticore:29.0.2

# Первичное создание учётных записей — до штатного entrypoint образа, который вызывается в конце.
COPY ./development/manticore/entrypoint.sh /usr/local/bin/bescms-entrypoint.sh
RUN chmod +x /usr/local/bin/bescms-entrypoint.sh

ENTRYPOINT ["bescms-entrypoint.sh"]
CMD ["searchd", "-c", "/etc/manticoresearch/manticore.conf.sh", "--nodetach"]
