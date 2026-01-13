# Инструкция по скачиванию CSS файлов локально

## Описание

Плагин теперь поддерживает локальное хранение CSS файлов виджетов. Это позволяет:
- Не зависеть от CDN
- Ускорить загрузку страниц
- Работать офлайн

## Как скачать CSS файлы

### Вариант 1: Использование скрипта download-css.php

1. Убедитесь, что у вас установлен PHP и доступен из командной строки
2. Откройте терминал в корневой папке плагина
3. Запустите команду:
   ```bash
   php download-css.php
   ```

Скрипт автоматически:
- Создаст необходимые папки
- Скачает все CSS файлы виджетов
- Заменит пути к изображениям на локальные

### Вариант 2: Ручное скачивание через браузер

Если PHP недоступен, вы можете скачать файлы вручную:

1. Создайте папку: `static/css/widget-presetted-css/v2/`
2. Скачайте CSS файлы по шаблону:
   ```
   https://cdn.trustindex.io/assets/widget-presetted-css/v2/{styleId}-{setId}.css
   ```
   
   Например:
   - `4-light-background.css`
   - `4-drop-shadow.css`
   - `5-light-background.css`
   - и т.д.

3. Сохраните файлы в папку `static/css/widget-presetted-css/v2/`

4. Скачайте `ti-preview-box.css`:
   ```
   https://cdn.trustindex.io/assets/ti-preview-box.css
   ```
   Сохраните в `static/css/ti-preview-box.css`

### Вариант 3: Использование wget или curl

```bash
# Создать папку
mkdir -p static/css/widget-presetted-css/v2

# Скачать файлы (пример для нескольких стилей)
wget https://cdn.trustindex.io/assets/widget-presetted-css/v2/4-light-background.css -O static/css/widget-presetted-css/v2/4-light-background.css
wget https://cdn.trustindex.io/assets/widget-presetted-css/v2/4-drop-shadow.css -O static/css/widget-presetted-css/v2/4-drop-shadow.css
wget https://cdn.trustindex.io/assets/widget-presetted-css/v2/5-light-background.css -O static/css/widget-presetted-css/v2/5-light-background.css

# Скачать preview CSS
wget https://cdn.trustindex.io/assets/ti-preview-box.css -O static/css/ti-preview-box.css
```

## Как это работает

Плагин автоматически проверяет наличие локальных CSS файлов:
- Если файл существует локально → используется локальный файл
- Если файла нет → загружается с CDN

Это означает, что вы можете скачивать файлы постепенно, по мере необходимости.

## Список всех возможных CSS файлов

Стили (style-id): 4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,44,45,46,47,48,52,53,54,55,56,57,58,59,60,61,62,79,80,81,95,97,98,99,100,101,102,103,104,105,106,107,108,109,110,111,112,113,114,115,116,117,118,119,120,121,122,123,124,125,126,127,128,129,130

Наборы стилей (set-id): light-background, light-background-large, ligth-border, ligth-border-3d-large, ligth-border-large, ligth-border-large-red, drop-shadow, drop-shadow-large, light-minimal, light-minimal-large, soft, light-clean, light-square, light-background-border, blue, light-background-large-purple, light-background-image, dark-background

Формат имени файла: `{styleId}-{setId}.css`

## Примечания

- После скачивания файлов рекомендуется очистить кэш WordPress
- Локальные файлы будут автоматически использоваться при следующей загрузке страницы
- Если файл не найден локально, плагин автоматически загрузит его с CDN
