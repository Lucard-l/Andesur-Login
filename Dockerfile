FROM serversideup/php:8.3-cli
WORKDIR /app
CMD ["php", "-S", "0.0.0.0:8080", "router.php"]
