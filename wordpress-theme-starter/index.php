<?php
/**
 * Main template file for Штори ПроФен theme
 *
 * @package WordPress
 * @subpackage ShoriProfen
 */

get_header(); ?>

<main id="primary" class="site-main">
    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1><?php echo get_bloginfo('name'); ?></h1>
                <p><?php echo get_bloginfo('description'); ?></p>
                <a href="#categories" class="btn btn-primary">Смотреть каталог</a>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="categories" id="categories">
        <div class="container">
            <h2>Категории товаров</h2>
            <div class="categories-grid">
                <?php
                $categories = get_terms([
                    'taxonomy' => 'product_cat',
                    'hide_empty' => false,
                    'parent' => 0
                ]);
                
                foreach ($categories as $category) {
                    $thumbnail_id = get_term_meta($category->term_id, 'thumbnail_id', true);
                    $image = wp_get_attachment_url($thumbnail_id);
                    ?>
                    <div class="category-card">
                        <?php if ($image) : ?>
                            <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($category->name); ?>">
                        <?php endif; ?>
                        <h3><?php echo esc_html($category->name); ?></h3>
                        <p><?php echo esc_html($category->description); ?></p>
                        <a href="<?php echo get_term_link($category); ?>" class="btn btn-secondary">Смотреть каталог</a>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Popular Products -->
    <section class="popular-products">
        <div class="container">
            <h2>Топ-3 модели плиссе</h2>
            <div class="products-grid">
                <?php
                $featured_products = new WP_Query([
                    'post_type' => 'product',
                    'posts_per_page' => 3,
                    'meta_key' => '_featured',
                    'meta_value' => 'yes'
                ]);
                
                if ($featured_products->have_posts()) :
                    while ($featured_products->have_posts()) : $featured_products->the_post();
                        global $product;
                        ?>
                        <div class="product-card">
                            <?php the_post_thumbnail('medium'); ?>
                            <h3><?php the_title(); ?></h3>
                            <div class="price">
                                от <?php echo $product->get_price_html(); ?>
                            </div>
                            <ul class="features">
                                <?php
                                $features = get_post_meta(get_the_ID(), '_features', true);
                                if ($features && is_array($features)) {
                                    foreach ($features as $feature) {
                                        echo '<li>' . esc_html($feature) . '</li>';
                                    }
                                }
                                ?>
                            </ul>
                            <a href="<?php the_permalink(); ?>" class="btn btn-primary">Подробнее</a>
                        </div>
                        <?php
                    endwhile;
                    wp_reset_postdata();
                endif;
                ?>
            </div>
        </div>
    </section>

    <!-- Visualizations Section -->
    <section class="visualizations">
        <div class="container">
            <h2>Вдохновение для вашего интерьера</h2>
            <div class="visualization-grid">
                <?php
                $visualizations = new WP_Query([
                    'post_type' => 'visualization',
                    'posts_per_page' => 6,
                    'meta_key' => '_featured_visualization',
                    'meta_value' => 'yes'
                ]);
                
                if ($visualizations->have_posts()) :
                    while ($visualizations->have_posts()) : $visualizations->the_post();
                        $room_type = get_post_meta(get_the_ID(), '_room_type', true);
                        $style = get_post_meta(get_the_ID(), '_style', true);
                        $color_scheme = get_post_meta(get_the_ID(), '_color_scheme', true);
                        ?>
                        <div class="visualization-card">
                            <?php the_post_thumbnail('large'); ?>
                            <div class="visualization-info">
                                <h3><?php the_title(); ?></h3>
                                <div class="visualization-meta">
                                    <span class="meta-item">
                                        <span class="meta-label">Помещение:</span>
                                        <span class="meta-value"><?php echo esc_html($room_type); ?></span>
                                    </span>
                                    <span class="meta-item">
                                        <span class="meta-label">Стиль:</span>
                                        <span class="meta-value"><?php echo esc_html($style); ?></span>
                                    </span>
                                </div>
                                <a href="<?php the_permalink(); ?>" class="btn btn-secondary">Подробнее</a>
                            </div>
                        </div>
                        <?php
                    endwhile;
                    wp_reset_postdata();
                endif;
                ?>
            </div>
        </div>
    </section>

    <!-- Partners Section -->
    <section class="partners">
        <div class="container">
            <h2>Наши партнеры</h2>
            <div class="partner-map" id="partner-map">
                <!-- Карта будет загружена через JavaScript -->
            </div>
            <div class="partner-list">
                <?php
                $partners = new WP_Query([
                    'post_type' => 'partner',
                    'posts_per_page' => 6
                ]);
                
                if ($partners->have_posts()) :
                    while ($partners->have_posts()) : $partners->the_post();
                        $rating = get_post_meta(get_the_ID(), '_rating', true);
                        $address = get_post_meta(get_the_ID(), '_address', true);
                        ?>
                        <div class="partner-card">
                            <?php the_post_thumbnail('thumbnail'); ?>
                            <h4><?php the_title(); ?></h4>
                            <div class="partner-rating">
                                <?php
                                for ($i = 1; $i <= 5; $i++) {
                                    echo $i <= $rating ? '★' : '☆';
                                }
                                ?>
                            </div>
                            <p><?php echo esc_html($address); ?></p>
                            <a href="<?php the_permalink(); ?>" class="btn btn-secondary">Подробнее</a>
                        </div>
                        <?php
                    endwhile;
                    wp_reset_postdata();
                endif;
                ?>
            </div>
        </div>
    </section>

    <!-- Magazine Section -->
    <section class="magazine">
        <div class="container">
            <h2>Журнал идей</h2>
            <div class="magazine-grid">
                <?php
                $magazine_posts = new WP_Query([
                    'post_type' => 'post',
                    'posts_per_page' => 3,
                    'category_name' => 'magazine'
                ]);
                
                if ($magazine_posts->have_posts()) :
                    while ($magazine_posts->have_posts()) : $magazine_posts->the_post();
                        ?>
                        <article class="magazine-card">
                            <?php the_post_thumbnail('medium'); ?>
                            <div class="magazine-content">
                                <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                <div class="post-meta">
                                    <span class="date"><?php echo get_the_date(); ?></span>
                                    <span class="category"><?php the_category(', '); ?></span>
                                </div>
                                <p><?php the_excerpt(); ?></p>
                                <a href="<?php the_permalink(); ?>" class="read-more">Читать далее →</a>
                            </div>
                        </article>
                        <?php
                    endwhile;
                    wp_reset_postdata();
                endif;
                ?>
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>
