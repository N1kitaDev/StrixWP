# Strix Google Reviews - Интеграция с Strix Box

## Быстрый старт

### 1. Активация плагина

1. Загрузите плагин в `/wp-content/plugins/strix-google-review/`
2. Активируйте плагин в админке WordPress
3. Перейдите в `Настройки → Strix Google Reviews`

### 2. Настройка лицензии

1. Введите ваш лицензионный ключ Strix Box
2. Нажмите "Сохранить изменения"
3. Нажмите "Подключить место Google"

### 3. Подключение Google Business

1. В открывшемся popup окне введите название или адрес вашего бизнеса
2. Нажмите "Search"
3. Выберите ваше место из списка результатов
4. Нажмите "Connect"

### 4. Отображение отзывов

Используйте шорткод для отображения отзывов:
```php
[strix_google_reviews]
```

## Шорткоды

### Основной шорткод

```php
[strix_google_reviews limit="5" show_rating="true" show_date="true" layout="list"]
```

#### Параметры:

- **limit** (число) - Количество отзывов для отображения (по умолчанию: 5)
- **show_rating** (true/false) - Показывать звездный рейтинг (по умолчанию: true)
- **show_date** (true/false) - Показывать дату отзыва (по умолчанию: true)
- **show_author** (true/false) - Показывать имя автора (по умолчанию: true)
- **layout** (list/grid) - Макет отображения (по умолчанию: list)
- **columns** (число) - Количество колонок для grid макета (по умолчанию: 3)

#### Примеры:

```php
<!-- Простой список из 3 отзывов -->
[strix_google_reviews limit="3"]

<!-- Сетка из 6 отзывов в 2 колонки -->
[strix_google_reviews limit="6" layout="grid" columns="2"]

<!-- Только текст отзывов без рейтинга и даты -->
[strix_google_reviews show_rating="false" show_date="false"]
```

## Программное использование

### Получение отзывов в коде

```php
$strix = new StrixGoogleReviewsSettings();

// Проверить подключение
if ($strix->is_place_connected()) {
    // Получить отзывы
    $reviews = $strix->get_reviews();
    
    if ($reviews) {
        foreach ($reviews as $review) {
            echo '<div>';
            echo '<h4>' . esc_html($review['author_name']) . '</h4>';
            echo '<p>Рейтинг: ' . $review['rating'] . '/5</p>';
            echo '<p>' . esc_html($review['text']) . '</p>';
            echo '</div>';
        }
    }
}
```

### Получение настроек

```php
$strix = new StrixGoogleReviewsSettings();
$settings = $strix->get_settings();

$license_key = $settings['license_key'] ?? '';
$place_id = $settings['place_id'] ?? '';
$place_name = $settings['place_name'] ?? '';
```

## Хуки и фильтры

### Actions

```php
// После успешного подключения места
do_action('strix_place_connected', $place_id, $place_name, $place_address);

// После получения отзывов
do_action('strix_reviews_loaded', $reviews, $place_id);

// Перед отправкой API запроса
do_action('strix_before_api_request', $endpoint, $data);
```

### Filters

```php
// Фильтр настроек API
$api_settings = apply_filters('strix_api_settings', [
    'base_url' => 'https://strixbox.com',
    'timeout' => 30
]);

// Фильтр отзывов перед отображением
$reviews = apply_filters('strix_reviews_before_display', $reviews, $atts);

// Фильтр HTML отзыва
$review_html = apply_filters('strix_review_html', $html, $review, $atts);
```

## Кастомизация

### CSS стили

Плагин подключает стили `assets/css/strix-frontend.css`. Вы можете переопределить их в своей теме:

```css
/* Стили для контейнера отзывов */
.strix-reviews-container {
    margin: 20px 0;
}

/* Стили для отдельного отзыва */
.strix-review-item {
    background: #fff;
    border: 1px solid #e1e1e1;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

/* Стили для звезд рейтинга */
.strix-star-full {
    color: #ffa500;
}
```

### Кастомный шаблон отзывов

Создайте файл `strix-reviews-template.php` в вашей теме:

```php
<?php
// Кастомный шаблон для отзывов
foreach ($reviews as $review) {
    ?>
    <div class="my-custom-review">
        <h3><?php echo esc_html($review['author_name']); ?></h3>
        <div class="rating"><?php echo str_repeat('★', $review['rating']); ?></div>
        <p><?php echo esc_html($review['text']); ?></p>
        <small><?php echo date('d.m.Y', strtotime($review['time'])); ?></small>
    </div>
    <?php
}
```

## Безопасность

### AJAX Nonce

Все AJAX запросы защищены nonce:
```javascript
strixAjax.nonce // Автоматически генерируется
```

### Права доступа

Настройки плагина доступны только пользователям с правами `manage_options`.

### HMAC проверка

Webhook от Strix Box проверяется HMAC подписью для защиты от подделки.

## Кэширование

### WordPress Transients

Отзывы кэшируются на 15 минут:
- Ключ: `strix_reviews_{md5(place_id)}`
- Очистка кэша при отключении места

### Принудительная очистка кэша

```php
// Очистить кэш отзывов
$place_id = 'ChIJ...';
$cache_key = 'strix_reviews_' . md5($place_id);
delete_transient($cache_key);
```

## Отладка

### Включение отладки WordPress

```php
// В wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

### Логирование

```php
// Добавить в functions.php для отладки
add_action('strix_before_api_request', function($endpoint, $data) {
    error_log('Strix API Request: ' . $endpoint . ' - ' . print_r($data, true));
});
```

## Troubleshooting

### Popup не открывается

1. Проверьте, что лицензионный ключ введен и сохранен
2. Убедитесь, что браузер разрешает всплывающие окна
3. Проверьте консоль браузера на ошибки JavaScript

### Отзывы не загружаются

1. Проверьте подключение к интернету
2. Убедитесь, что место подключено (Place ID сохранен)
3. Проверьте логи WordPress в `/wp-content/debug.log`
4. Проверьте, что Strix Box API доступен

### Ошибка "Недействительная лицензия"

1. Проверьте правильность лицензионного ключа
2. Убедитесь, что лицензия активна
3. Проверьте, что домен сайта соответствует лицензии

### Ошибка HMAC подписи

1. Убедитесь, что серверный секрет совпадает в Laravel и WordPress
2. Проверьте, что webhook приходит от правильного источника
3. Проверьте логи для деталей ошибки

## Совместимость

- **WordPress**: 6.2+
- **PHP**: 7.0+
- **Браузеры**: Chrome 60+, Firefox 55+, Safari 12+, Edge 79+

## Файловая структура

```
strix-google-review/
├── assets/
│   ├── css/
│   │   ├── strix-admin.css          # Стили админки
│   │   └── strix-frontend.css       # Стили фронтенда
│   └── js/
│       └── strix-admin.js           # JavaScript админки
├── includes/
│   ├── strix-admin-settings.php     # Класс настроек
│   └── strix-shortcodes.php         # Шорткоды
├── INTEGRATION.md                   # Документация интеграции
├── README_INTEGRATION.md            # Этот файл
└── strix-google-reviews.php         # Основной файл плагина
```

## Changelog

### v1.0.0
- Первая версия интеграции с Strix Box
- Popup подключение Google Business места
- Шорткод для отображения отзывов
- AJAX интерфейс в админке
- Кэширование отзывов
- HMAC защита webhook

## Поддержка

Для получения поддержки:
1. Проверьте логи WordPress
2. Убедитесь, что все настройки корректны
3. Проверьте совместимость версий
4. Обратитесь к документации INTEGRATION.md