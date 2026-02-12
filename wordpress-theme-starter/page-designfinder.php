<?php
/**
 * Template Name: Designfinder
 * Design & Inspiration page with filtering and AR functionality
 */

get_header(); ?>

<div class="designfinder-container">
    <header class="page-header">
        <div class="container">
            <h1>Дизайн и Вдохновение</h1>
            <p>Найдите идеальное решение для вашего интерьера</p>
        </div>
    </header>

    <section class="designfinder-filters">
        <div class="container">
            <form id="designfinder-filters" class="filter-form">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="filter-room-type">Тип помещения</label>
                        <select id="filter-room-type" name="room_type">
                            <option value="">Все помещения</option>
                            <?php
                            $room_types = get_terms(['taxonomy' => 'room_type', 'hide_empty' => true]);
                            foreach ($room_types as $term) {
                                echo '<option value="' . $term->slug . '">' . $term->name . '</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="filter-style">Стиль интерьера</label>
                        <select id="filter-style" name="interior_style">
                            <option value="">Все стили</option>
                            <?php
                            $styles = get_terms(['taxonomy' => 'interior_style', 'hide_empty' => true]);
                            foreach ($styles as $term) {
                                echo '<option value="' . $term->slug . '">' . $term->name . '</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="filter-window-type">Тип окон</label>
                        <select id="filter-window-type" name="window_type">
                            <option value="">Все типы</option>
                            <?php
                            $window_types = get_terms(['taxonomy' => 'window_type', 'hide_empty' => true]);
                            foreach ($window_types as $term) {
                                echo '<option value="' . $term->slug . '">' . $term->name . '</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="filter-visual-type">Тип визуализации</label>
                        <select id="filter-visual-type" name="visual_type">
                            <option value="">Все типы</option>
                            <?php
                            $visual_types = get_terms(['taxonomy' => 'visual_type', 'hide_empty' => true]);
                            foreach ($visual_types as $term) {
                                echo '<option value="' . $term->slug . '">' . $term->name . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">Применить фильтры</button>
                    <button type="button" class="btn btn-secondary" id="clear-filters">Сбросить</button>
                </div>
            </form>
        </div>
    </section>

    <section class="designfinder-content">
        <div class="container">
            <div class="content-grid">
                <div class="visualizations-section">
                    <div class="section-header">
                        <h2>Визуализации</h2>
                        <div class="view-options">
                            <button class="view-btn active" data-view="grid">Сетка</button>
                            <button class="view-btn" data-view="list">Список</button>
                        </div>
                    </div>

                    <div id="visualizations-grid" class="visualizations-grid">
                        <!-- Visualizations will be loaded via AJAX -->
                        <div class="loading-placeholder">
                            <div class="spinner"></div>
                            <p>Загрузка визуализаций...</p>
                        </div>
                    </div>

                    <div class="load-more-container">
                        <button class="btn btn-outline" id="load-more">Загрузить еще</button>
                    </div>
                </div>

                <aside class="designfinder-sidebar">
                    <div class="sidebar-section">
                        <h3>Популярные стили</h3>
                        <div class="popular-styles">
                            <?php
                            $popular_styles = get_terms([
                                'taxonomy' => 'interior_style',
                                'hide_empty' => true,
                                'orderby' => 'count',
                                'number' => 5
                            ]);
                            
                            foreach ($popular_styles as $style) {
                                echo '<a href="#" class="style-tag" data-style="' . $style->slug . '">';
                                echo '<span class="style-name">' . $style->name . '</span>';
                                echo '<span class="style-count">' . $style->count . '</span>';
                                echo '</a>';
                            }
                            ?>
                        </div>
                    </div>

                    <div class="sidebar-section">
                        <h3>AR Демонстрация</h3>
                        <div class="ar-demo-card">
                            <div class="ar-demo-image">
                                <img src="<?php echo get_template_directory_uri(); ?>/images/ar-demo.jpg" alt="AR Демонстрация">
                            </div>
                            <div class="ar-demo-content">
                                <p>Попробуйте нашу AR технологию для просмотра штор в вашем помещении</p>
                                <button class="btn btn-primary ar-demo-btn">🥽 Попробовать AR</button>
                            </div>
                        </div>
                    </div>

                    <div class="sidebar-section">
                        <h3>Советы по выбору</h3>
                        <div class="tips-list">
                            <div class="tip-item">
                                <h4>🛏️ Для спальни</h4>
                                <p>Выбирайте ткани в спокойных тонах с blackout эффектом для качественного сна</p>
                            </div>
                            <div class="tip-item">
                                <h4>🍳 Для кухни</h4>
                                <p>Отдайте предпочтение практичным материалам, которые легко мыть</p>
                            </div>
                            <div class="tip-item">
                                <h4>🛋️ Для гостиной</h4>
                                <p>Сочетайте эстетику и функциональность, выбирайте многослойные решения</p>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    <section class="inspiration-gallery">
        <div class="container">
            <div class="section-header">
                <h2>Готовые решения</h2>
                <p>Реальные проекты наших клиентов</p>
            </div>

            <div class="gallery-grid">
                <?php
                // Load sample gallery items
                $gallery_items = [
                    ['image' => 'bedroom-modern.jpg', 'title' => 'Современная спальня', 'category' => 'Спальня'],
                    ['image' => 'kitchen-scandinavian.jpg', 'title' => 'Скандинавская кухня', 'category' => 'Кухня'],
                    ['image' => 'living-loft.jpg', 'title' => 'Гостиная в стиле лофт', 'category' => 'Гостиная'],
                    ['image' => 'office-minimal.jpg', 'title' => 'Минималистичный офис', 'category' => 'Офис'],
                    ['image' => 'kids-colorful.jpg', 'title' => 'Яркая детская', 'category' => 'Детская'],
                    ['image' => 'attic-cozy.jpg', 'title' => 'Уютная мансарда', 'category' => 'Мансарда']
                ];

                foreach ($gallery_items as $item) {
                    echo '<div class="gallery-item">';
                    echo '<div class="gallery-image">';
                    echo '<img src="' . get_template_directory_uri() . '/images/gallery/' . $item['image'] . '" alt="' . esc_attr($item['title']) . '">';
                    echo '<div class="gallery-overlay">';
                    echo '<h4>' . $item['title'] . '</h4>';
                    echo '<span class="gallery-category">' . $item['category'] . '</span>';
                    echo '<button class="btn btn-white view-project">Смотреть проект</button>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    </section>

    <section class="color-schemes">
        <div class="container">
            <div class="section-header">
                <h2>Цветовые решения</h2>
                <p>Подберите идеальную палитру для вашего интерьера</p>
            </div>

            <div class="color-groups">
                <div class="color-group">
                    <h3>Нейтральные тона</h3>
                    <div class="color-palette">
                        <div class="color-swatch" style="background-color: #FFFFFF;" data-color="#FFFFFF"></div>
                        <div class="color-swatch" style="background-color: #F5F5F5;" data-color="#F5F5F5"></div>
                        <div class="color-swatch" style="background-color: #E8E8E8;" data-color="#E8E8E8"></div>
                        <div class="color-swatch" style="background-color: #D3D3D3;" data-color="#D3D3D3"></div>
                        <div class="color-swatch" style="background-color: #A9A9A9;" data-color="#A9A9A9"></div>
                    </div>
                    <p>Универсальные решения для любого интерьера</p>
                </div>

                <div class="color-group">
                    <h3>Теплые оттенки</h3>
                    <div class="color-palette">
                        <div class="color-swatch" style="background-color: #FFE4B5;" data-color="#FFE4B5"></div>
                        <div class="color-swatch" style="background-color: #F4A460;" data-color="#F4A460"></div>
                        <div class="color-swatch" style="background-color: #CD853F;" data-color="#CD853F"></div>
                        <div class="color-swatch" style="background-color: #8B4513;" data-color="#8B4513"></div>
                        <div class="color-swatch" style="background-color: #A0522D;" data-color="#A0522D"></div>
                    </div>
                    <p>Создают уют и комфорт в помещении</p>
                </div>

                <div class="color-group">
                    <h3>Холодные оттенки</h3>
                    <div class="color-palette">
                        <div class="color-swatch" style="background-color: #E0FFFF;" data-color="#E0FFFF"></div>
                        <div class="color-swatch" style="background-color: #87CEEB;" data-color="#87CEEB"></div>
                        <div class="color-swatch" style="background-color: #4682B4;" data-color="#4682B4"></div>
                        <div class="color-swatch" style="background-color: #191970;" data-color="#191970"></div>
                        <div class="color-swatch" style="background-color: #000080;" data-color="#000080"></div>
                    </div>
                    <p>Расширяют пространство и создают свежесть</p>
                </div>

                <div class="color-group">
                    <h3>Яркие акценты</h3>
                    <div class="color-palette">
                        <div class="color-swatch" style="background-color: #FF6B6B;" data-color="#FF6B6B"></div>
                        <div class="color-swatch" style="background-color: #4ECDC4;" data-color="#4ECDC4"></div>
                        <div class="color-swatch" style="background-color: #45B7D1;" data-color="#45B7D1"></div>
                        <div class="color-swatch" style="background-color: #96CEB4;" data-color="#96CEB4"></div>
                        <div class="color-swatch" style="background-color: #FFEAA7;" data-color="#FFEAA7"></div>
                    </div>
                    <p>Добавляют энергии и индивидуальности</p>
                </div>
            </div>
        </div>
    </section>
</div>

<?php get_footer(); ?>
