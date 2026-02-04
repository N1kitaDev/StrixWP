# Интеграция WordPress плагина с Strix Box

Этот документ описывает контракт интеграции между WordPress плагином (StrixReviewsPlugin) и Laravel админкой (StrixBoxAdmin).

## Архитектура системы

```
WordPress Plugin ←→ Laravel Popup ←→ Laravel API ←→ Google Places API
```

## Компоненты WordPress Plugin

### 1. Страница настроек

**URL**: `/wp-admin/options-general.php?page=strix-google-reviews`
**Класс**: `StrixGoogleReviewsSettings`

Функционал:
- Ввод лицензионного ключа
- Отображение статуса подключения
- Кнопка "Подключить место Google"
- Загрузка и отображение отзывов

### 2. JavaScript обработчик Connect

**Файл**: `assets/js/strix-admin.js`

Функционал:
- Генерация случайного state параметра
- Открытие popup окна на strixbox.com
- Обработка результата подключения
- AJAX запросы к WordPress

### 3. AJAX обработчики

#### Подключение Google места
**Action**: `wp_ajax_strixbox_google_connected`
**Метод**: `StrixGoogleReviewsSettings::handle_google_connected()`

Получает webhook от Laravel с данными места:
```json
{
  "state": "случайная строка",
  "place_id": "ChIJ...",
  "place_name": "Название места",
  "place_address": "Адрес места",
  "signature": "HMAC SHA256 подпись"
}
```

#### Получение отзывов
**Action**: `wp_ajax_strix_get_reviews`
**Метод**: `StrixGoogleReviewsSettings::get_reviews_ajax()`

Делает запрос к Laravel API и возвращает отзывы.

#### Сохранение state
**Action**: `wp_ajax_strix_save_state`
**Метод**: `StrixGoogleReviewsSettings::save_state_ajax()`

Сохраняет state в transient на 10 минут.

#### Отключение места
**Action**: `wp_ajax_strix_disconnect_place`
**Метод**: `StrixGoogleReviewsSettings::disconnect_place_ajax()`

Удаляет данные подключенного места.

### 4. Шорткоды

**Класс**: `StrixGoogleReviewsShortcodes`

#### Основной шорткод
```php
[strix_google_reviews limit="5" show_rating="true" show_date="true" layout="grid" columns="3"]
```

Параметры:
- `limit` - количество отзывов (по умолчанию 5)
- `show_rating` - показывать рейтинг (true/false)
- `show_date` - показывать дату (true/false)
- `show_author` - показывать автора (true/false)
- `layout` - макет отображения (list/grid/slider)
- `columns` - количество колонок для grid (по умолчанию 3)

## Интеграция с Laravel

### Popup окно Connect

При нажатии кнопки "Подключить место Google" открывается popup:

```
https://strixbox.com/connect/google?
  license_key={лицензионный ключ}&
  site_url={home_url()}&
  callback_url={admin_url('admin-ajax.php?action=strixbox_google_connected')}&
  state={случайная строка}
```

### API запросы к Laravel

#### Получение отзывов
```php
$response = wp_remote_post('https://strixbox.com/api/v1/google/reviews/latest', [
    'headers' => ['Content-Type' => 'application/json'],
    'body' => json_encode([
        'place_id' => $place_id,
        'license_key' => $license_key,
        'domain' => home_url(),
    ]),
    'timeout' => 30,
]);
```

## Безопасность

### HMAC проверка webhook

```php
private function verify_webhook_signature($data, $license_key) {
    $signature = $data['signature'];
    unset($data['signature']);
    
    ksort($data);
    $payload = json_encode($data);
    
    $server_secret = 'your-secret-key-here'; // Должен совпадать с Laravel
    $secret = $server_secret . $license_key;
    $expected_signature = hash_hmac('sha256', $payload, $secret);
    
    return hash_equals($expected_signature, $signature);
}
```

### Nonce проверка

Все AJAX запросы защищены WordPress nonce:
```php
check_ajax_referer('strix_nonce', 'nonce');
```

### Проверка прав доступа

```php
if (!current_user_can('manage_options')) {
    wp_die('Недостаточно прав доступа');
}
```

## Хранение данных

### Опции WordPress

Все настройки хранятся в одной опции: `strix_google_reviews_settings`

```php
[
    'license_key' => 'лицензионный ключ',
    'place_id' => 'ChIJ...',
    'place_name' => 'Название места',
    'place_address' => 'Адрес места',
    'connected_at' => '2026-02-04 12:00:00'
]
```

### Transients (кэш)

- **State**: `strix_connect_state` (10 минут)
- **Отзывы**: `strix_reviews_{md5(place_id)}` (15 минут)

## Стили и скрипты

### Админка
- **CSS**: `assets/css/strix-admin.css`
- **JS**: `assets/js/strix-admin.js`

### Фронтенд
- **CSS**: `assets/css/strix-frontend.css`

## Хуки и фильтры

### Actions
- `admin_menu` - добавление страницы настроек
- `admin_init` - инициализация настроек
- `admin_enqueue_scripts` - подключение скриптов админки
- `wp_enqueue_scripts` - подключение стилей фронтенда

### Shortcodes
- `strix_google_reviews` - отображение отзывов

## Обработка ошибок

### AJAX ошибки
```php
wp_send_json_error('Сообщение об ошибке');
wp_send_json_success(['data' => $data]);
```

### Отображение ошибок
```html
<p class="strix-error">Google место не подключено. Настройте плагин в админке.</p>
<p class="strix-no-reviews">Отзывы не найдены.</p>
```

## Публичные методы

### StrixGoogleReviewsSettings

```php
// Получить настройки
$settings = $instance->get_settings();

// Проверить подключение места
$connected = $instance->is_place_connected();

// Получить отзывы
$reviews = $instance->get_reviews();
```

## Примеры использования

### В теме WordPress

```php
// Проверить подключение
$strix = new StrixGoogleReviewsSettings();
if ($strix->is_place_connected()) {
    $reviews = $strix->get_reviews();
    // Отобразить отзывы
}

// Или использовать шорткод
echo do_shortcode('[strix_google_reviews limit="3" layout="grid"]');
```

### В виджете

```php
class StrixReviewsWidget extends WP_Widget {
    public function widget($args, $instance) {
        echo do_shortcode('[strix_google_reviews limit="5"]');
    }
}
```

## Совместимость

- **WordPress**: 6.2+
- **PHP**: 7.0+
- **Зависимости**: jQuery (для админки)

## Локализация

Текстовый домен: `strix-google-reviews`
Папка переводов: `/languages/`

## Отладка

### Включение отладки

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

### Логирование

```php
error_log('Strix Debug: ' . print_r($data, true));
```

## Версионирование

Текущая версия плагина: **1.0.0**

При обновлениях проверять совместимость с API Laravel.