/**
 * Strix Google Reviews - Admin JavaScript
 */

jQuery(document).ready(function($) {
    
    // Обработчик кнопки "Подключить место Google"
    $('#strix-connect-btn').on('click', function(e) {
        e.preventDefault();
        
        // Проверяем, что лицензионный ключ введен
        var licenseKey = $('#license_key').val().trim();
        if (!licenseKey) {
            alert('Пожалуйста, введите лицензионный ключ и сохраните настройки перед подключением места.');
            return;
        }
        
        // Генерируем случайный state для защиты от CSRF
        var state = generateRandomState();
        
        // Сохраняем state на сервере
        $.post(strixAjax.ajaxurl, {
            action: 'strix_save_state',
            nonce: strixAjax.nonce,
            state: state
        }, function(response) {
            if (response.success) {
                openConnectPopup(licenseKey, state);
            } else {
                alert('Ошибка: ' + response.data);
            }
        });
    });
    
    // Обработчик кнопки "Отключить место"
    $('#strix-disconnect-btn').on('click', function(e) {
        e.preventDefault();
        
        if (!confirm('Вы уверены, что хотите отключить текущее место Google?')) {
            return;
        }
        
        $.post(strixAjax.ajaxurl, {
            action: 'strix_disconnect_place',
            nonce: strixAjax.nonce
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Ошибка: ' + response.data);
            }
        });
    });
    
    // Обработчик кнопки "Загрузить отзывы"
    $('#strix-load-reviews').on('click', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var $container = $('#strix-reviews-container');
        
        $button.prop('disabled', true).text('Загрузка...');
        $container.html('<p>Загрузка отзывов...</p>');
        
        $.post(strixAjax.ajaxurl, {
            action: 'strix_get_reviews',
            nonce: strixAjax.nonce
        }, function(response) {
            if (response.success) {
                displayReviews(response.data.reviews, $container);
                if (response.data.cached) {
                    $container.prepend('<p><em>Отзывы загружены из кэша</em></p>');
                }
            } else {
                $container.html('<p style="color: red;">Ошибка: ' + response.data + '</p>');
            }
        }).always(function() {
            $button.prop('disabled', false).text('Загрузить отзывы');
        });
    });
    
    /**
     * Открыть popup окно для подключения места
     */
    function openConnectPopup(licenseKey, state) {
        var params = new URLSearchParams({
            license_key: licenseKey,
            site_url: strixAjax.siteUrl,
            callback_url: strixAjax.callbackUrl,
            state: state
        });
        
        var popupUrl = strixAjax.connectUrl + '?' + params.toString();
        
        // Открываем popup окно
        var popup = window.open(
            popupUrl,
            'strix_connect',
            'width=800,height=600,scrollbars=yes,resizable=yes,toolbar=no,menubar=no,location=no,status=no'
        );
        
        if (!popup) {
            alert('Не удалось открыть popup окно. Пожалуйста, разрешите всплывающие окна для этого сайта.');
            return;
        }
        
        // Проверяем, закрылось ли окно
        var checkClosed = setInterval(function() {
            if (popup.closed) {
                clearInterval(checkClosed);
                // Перезагружаем страницу через небольшую задержку
                setTimeout(function() {
                    location.reload();
                }, 1000);
            }
        }, 1000);
    }
    
    /**
     * Генерировать случайный state
     */
    function generateRandomState() {
        var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        var result = '';
        for (var i = 0; i < 32; i++) {
            result += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return result;
    }
    
    /**
     * Отобразить отзывы
     */
    function displayReviews(reviews, $container) {
        if (!reviews || reviews.length === 0) {
            $container.html('<p>Отзывы не найдены.</p>');
            return;
        }
        
        var html = '<div class="strix-reviews-list">';
        
        reviews.forEach(function(review) {
            html += '<div class="strix-review-item" style="border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 5px;">';
            
            // Заголовок с автором и рейтингом
            html += '<div class="strix-review-header" style="display: flex; justify-content: space-between; margin-bottom: 10px;">';
            html += '<strong>' + escapeHtml(review.author_name) + '</strong>';
            html += '<div class="strix-rating">' + generateStars(review.rating) + '</div>';
            html += '</div>';
            
            // Текст отзыва
            if (review.text) {
                html += '<div class="strix-review-text" style="margin-bottom: 10px;">';
                html += '<p>' + escapeHtml(review.text) + '</p>';
                html += '</div>';
            }
            
            // Дата
            if (review.time) {
                html += '<div class="strix-review-date" style="color: #666; font-size: 12px;">';
                html += formatDate(review.time);
                html += '</div>';
            }
            
            html += '</div>';
        });
        
        html += '</div>';
        $container.html(html);
    }
    
    /**
     * Генерировать звезды для рейтинга
     */
    function generateStars(rating) {
        var stars = '';
        for (var i = 1; i <= 5; i++) {
            if (i <= rating) {
                stars += '★';
            } else {
                stars += '☆';
            }
        }
        return '<span style="color: #ffa500;">' + stars + '</span>';
    }
    
    /**
     * Экранирование HTML
     */
    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    /**
     * Форматирование даты
     */
    function formatDate(dateString) {
        var date = new Date(dateString);
        return date.toLocaleDateString('ru-RU') + ' ' + date.toLocaleTimeString('ru-RU', {
            hour: '2-digit',
            minute: '2-digit'
        });
    }
    
});

// Дополнительные AJAX обработчики для сохранения state и отключения места
jQuery(document).ready(function($) {
    
    // Добавляем обработчики для дополнительных AJAX действий
    if (typeof strixAjax !== 'undefined') {
        
        // Обработчик для сохранения state (добавляем в PHP)
        $(document).on('strix_save_state_needed', function(e, state) {
            $.post(strixAjax.ajaxurl, {
                action: 'strix_save_state',
                nonce: strixAjax.nonce,
                state: state
            });
        });
        
    }
    
});