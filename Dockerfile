# استفاده از ایمیج رسمی PHP به همراه وب‌سرور Apache
FROM php:8.2-apache

# نصب اکستنشن‌های مورد نیاز برای ارتباط با دیتابیس MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# کپی کردن فایل‌های پروژه از فولدر main به روت وب‌سرور آپاچی
COPY main/ /var/www/html/

# تنظیم دسترسی‌های فایل‌ها برای وب‌سرور
RUN chown -R www-data:www-data /var/www/html

# باز کردن پورت ۸۰ برای دسترسی وب
EXPOSE 80
