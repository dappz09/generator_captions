FROM php:8.2-apache

# Salin semua file dari proyek ke dalam root direktori web server Apache
COPY . /var/www/html/

# Ubah hak akses direktori agar Apache dapat membacanya
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Ekspos port 80 (dibutuhkan oleh Render)
EXPOSE 80
