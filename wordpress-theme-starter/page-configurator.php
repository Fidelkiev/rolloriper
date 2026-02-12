<?php
/**
 * Template Name: Configurator
 * Interactive product configurator with AR support
 */

get_header(); ?>

<div class="configurator-container">
    <header class="page-header">
        <div class="container">
            <h1>Конфигуратор штор</h1>
            <p>Создайте идеальное решение для вашего окна</p>
        </div>
    </header>

    <div class="configurator-wrapper">
        <div class="container">
            <div class="configurator-progress">
                <div class="progress-bar">
                    <div class="configurator-progress-bar" style="width: 20%;"></div>
                </div>
                <div class="progress-text">
                    <span class="configurator-progress-text">Шаг 1 из 5</span>
                </div>
            </div>

            <div class="configurator-nav">
                <button class="nav-btn active" data-step="1">
                    <span class="nav-icon">🏠</span>
                    <span class="nav-label">Помещение</span>
                </button>
                <button class="nav-btn" data-step="2">
                    <span class="nav-icon">🪟</span>
                    <span class="nav-label">Окно</span>
                </button>
                <button class="nav-btn" data-step="3">
                    <span class="nav-icon">🎨</span>
                    <span class="nav-label">Продукт</span>
                </button>
                <button class="nav-btn" data-step="4">
                    <span class="nav-icon">🧵</span>
                    <span class="nav-label">Материал</span>
                </button>
                <button class="nav-btn" data-step="5">
                    <span class="nav-icon">⚙️</span>
                    <span class="nav-label">Опции</span>
                </button>
            </div>

            <div class="configurator-content">
                <!-- Step 1: Room Type -->
                <div class="configurator-step active" data-step="1" id="step-room-types">
                    <div class="step-header">
                        <h2>Выберите тип помещения</h2>
                        <p>Это поможет нам подобрать оптимальное решение</p>
                    </div>
                    <div class="configurator-options">
                        <!-- Options will be loaded via JavaScript -->
                    </div>
                    <div class="step-actions">
                        <button class="btn btn-outline video-instruction-btn" data-video="room-measurement">
                            📹 Как измерить окно
                        </button>
                    </div>
                </div>

                <!-- Step 2: Window Type -->
                <div class="configurator-step" data-step="2" id="step-window-types">
                    <div class="step-header">
                        <h2>Укажите тип окна</h2>
                        <p>Разные типы окон требуют разных решений</p>
                    </div>
                    <div class="configurator-options">
                        <!-- Options will be loaded via JavaScript -->
                    </div>
                    <div class="step-actions">
                        <button class="btn btn-secondary" onclick="ShoriProfenConfigurator.goToStep(1)">← Назад</button>
                    </div>
                </div>

                <!-- Step 3: Product Type -->
                <div class="configurator-step" data-step="3" id="step-product-types">
                    <div class="step-header">
                        <h2>Выберите тип штор</h2>
                        <p>Рекомендуемые решения для вашего помещения</p>
                    </div>
                    <div class="configurator-options">
                        <!-- Options will be loaded via JavaScript -->
                    </div>
                    <div class="step-actions">
                        <button class="btn btn-secondary" onclick="ShoriProfenConfigurator.goToStep(2)">← Назад</button>
                    </div>
                </div>

                <!-- Step 4: Material -->
                <div class="configurator-step" data-step="4" id="step-materials">
                    <div class="step-header">
                        <h2>Выберите материал и цвет</h2>
                        <p>Материал определяет внешний вид и функциональность</p>
                    </div>
                    <div class="configurator-options">
                        <!-- Options will be loaded via JavaScript -->
                    </div>
                    <div class="step-actions">
                        <button class="btn btn-secondary" onclick="ShoriProfenConfigurator.goToStep(3)">← Назад</button>
                    </div>
                </div>

                <!-- Step 5: Additional Options -->
                <div class="configurator-step" data-step="5" id="step-additional">
                    <div class="step-header">
                        <h2>Дополнительные опции</h2>
                        <p>Улучшите функциональность вашего решения</p>
                    </div>
                    <div class="configurator-options">
                        <!-- Options will be loaded via JavaScript -->
                    </div>
                    <div class="step-actions">
                        <button class="btn btn-secondary" onclick="ShoriProfenConfigurator.goToStep(4)">← Назад</button>
                    </div>
                </div>
            </div>

            <aside class="configurator-sidebar">
                <div class="configurator-summary">
                    <h3>Ваша конфигурация</h3>
                    <div class="summary-items">
                        <!-- Summary items will be populated dynamically -->
                    </div>
                    <div class="summary-total">
                        <span>Итого:</span>
                        <span class="amount">0 грн</span>
                    </div>
                </div>

                <div class="installation-service">
                    <div class="configurator-option">
                        <label class="checkbox-label">
                            <input type="checkbox" id="installation-service">
                            <span class="checkmark"></span>
                            <div class="option-content">
                                <h4>Монтаж под ключ</h4>
                                <p>Профессиональная установка с гарантией</p>
                                <span class="installation-price">от 1500 грн</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="configurator-actions">
                    <button class="btn btn-primary save-config" disabled>Сохранить конфигурацию</button>
                    <button class="btn btn-outline share-config">Поделиться</button>
                </div>

                <div class="ar-preview">
                    <h3>AR предпросмотр</h3>
                    <div class="ar-preview-placeholder">
                        <img src="<?php echo get_template_directory_uri(); ?>/images/ar-preview-placeholder.jpg" alt="AR Preview">
                        <button class="btn btn-primary ar-view-btn" disabled>🥽 Просмотр в AR</button>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>

<!-- Video Modal -->
<div class="video-modal" style="display: none;">
    <div class="video-content">
        <button class="video-close">×</button>
        <div class="video-wrapper">
            <iframe src="" frameborder="0" allowfullscreen></iframe>
        </div>
    </div>
</div>

<!-- Share Modal -->
<div class="share-modal" style="display: none;">
    <div class="share-content">
        <button class="share-close">×</button>
        <h3>Поделиться конфигурацией</h3>
        <div class="share-options">
            <button class="share-btn" data-platform="telegram">
                <span class="share-icon">📱</span>
                Telegram
            </button>
            <button class="share-btn" data-platform="viber">
                <span class="share-icon">💬</span>
                Viber
            </button>
            <button class="share-btn" data-platform="email">
                <span class="share-icon">📧</span>
                Email
            </button>
            <button class="share-btn" data-platform="copy">
                <span class="share-icon">📋</span>
                Копировать ссылку
            </button>
        </div>
        <div class="share-link">
            <input type="text" readonly value="">
            <button class="btn btn-outline copy-link">Копировать</button>
        </div>
    </div>
</div>

<?php get_footer(); ?>
