<?php
/**
 * Plugin Name: Movies Rewrites Demo
 * Description: CPT "Movies" with custom permalinks (/movies/%genre%/%postname%/), endpoint (/reviews/), year archive (/movies/year/2024/), and a custom feed.
 * Version: 1.0.0
 * Author: Vinay
 */

defined('ABSPATH') || exit;

class Movies_Rewrites_Demo {
    public static function init() {
        add_action('init', [__CLASS__, 'register_cpt_tax']);
        add_action('init', [__CLASS__, 'register_endpoint']);
        add_action('init', [__CLASS__, 'register_custom_rules']);
        add_filter('post_type_link', [__CLASS__, 'movies_permalink'], 10, 3);
        add_filter('the_content', [__CLASS__, 'reviews_endpoint_content']);
        add_action('init', [__CLASS__, 'register_movies_feed']);

        register_activation_hook(__FILE__, [__CLASS__, 'activate']);
        register_deactivation_hook(__FILE__, [__CLASS__, 'deactivate']);
    }

    // 1) CPT + Taxonomy
    public static function register_cpt_tax() {
        register_post_type('movie', [
            'label' => 'Movies',
            'public' => true,
            'has_archive' => 'movies',
            'rewrite' => [
                'slug' => 'movies/%genre%',
                'with_front' => false,
            ],
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'comments'],
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-format-video',
        ]);

        register_taxonomy('genre', ['movie'], [
            'label' => 'Genres',
            'hierarchical' => true,
            // /movies/action/ (taxonomy archive)
            'rewrite' => [
                'slug' => 'movies',
                'with_front' => false,
                'hierarchical' => true,
            ],
            'show_in_rest' => true,
        ]);
    }

    // Replace %genre% in movie permalinks with first assigned genre
    public static function movies_permalink($permalink, $post, $leavename) {
        if ($post->post_type !== 'movie') return $permalink;

        $terms = wp_get_object_terms($post->ID, 'genre');
        $genre = (is_array($terms) && !empty($terms) && !is_wp_error($terms)) ? $terms[0]->slug : 'uncategorized';

        return str_replace('%genre%', $genre, $permalink);
    }

    // 2) Endpoint: /movies/<genre>/<postname>/reviews/
    public static function register_endpoint() {
        add_rewrite_endpoint('reviews', EP_PERMALINK);
    }

    // Endpoint content attach
    public static function reviews_endpoint_content($content) {
        $has_reviews_endpoint = get_query_var('reviews', '');
        if (is_singular('movie') && $has_reviews_endpoint !== '') {
            $extra = '<h2>User Reviews</h2><p>Yaha par aap custom reviews, comments ya external API ka data dikha sakte ho.</p>';
            return $content . $extra;
        }
        return $content;
    }

    // 3) Custom rewrite rules (examples)
    public static function register_custom_rules() {
        // /movies/year/2024/ → movie posts from year=2024
        add_rewrite_rule('^movies/year/([0-9]{4})/?$', 'index.php?post_type=movie&year=$matches[1]', 'top');

        // /movies/genre/action/ → list movies in a genre
        add_rewrite_rule('^movies/genre/([^/]+)/?$', 'index.php?post_type=movie&genre=$matches[1]', 'top');
    }

    // 4) Custom feed: /feed/movies (or ?feed=movies)
    public static function register_movies_feed() {
        add_feed('movies', [__CLASS__, 'movies_feed_callback']);
    }

    public static function movies_feed_callback() {
        header('Content-Type: ' . feed_content_type('rss2') . '; charset=' . get_option('blog_charset'), true);
        echo '<?xml version="1.0" encoding="' . esc_attr(get_option('blog_charset')) . '"?>';
        ?>
<rss version="2.0">
 <channel>
  <title><?php bloginfo_rss('name'); ?> - Movies Feed</title>
  <link><?php bloginfo_rss('url'); ?></link>
  <description>Latest Movies</description>
  <language><?php bloginfo_rss('language'); ?></language>
  <?php
    $q = new WP_Query(['post_type' => 'movie', 'posts_per_page' => 10]);
    while ($q->have_posts()): $q->the_post(); ?>
    <item>
      <title><?php the_title_rss(); ?></title>
      <link><?php the_permalink_rss(); ?></link>
      <guid isPermaLink="false"><?php the_guid(); ?></guid>
      <pubDate><?php echo esc_html(get_post_time('r', true)); ?></pubDate>
      <description><![CDATA[<?php the_excerpt_rss(); ?>]]></description>
    </item>
  <?php endwhile; wp_reset_postdata(); ?>
 </channel>
</rss>
<?php
    }

    // Activation/Deactivation: Always flush rules
    public static function activate() {
        self::register_cpt_tax();
        self::register_endpoint();
        self::register_custom_rules();
        flush_rewrite_rules();
    }

    public static function deactivate() {
        flush_rewrite_rules();
    }
}

Movies_Rewrites_Demo::init();

// Add endpoints on init
add_action('init', function() {
    // Trailer endpoint - visible only on single movie pages
    add_rewrite_endpoint('trailer', EP_PERMALINK);

    // Behind-the-scenes endpoint - not linked to default masks
    add_rewrite_endpoint('behind-the-scenes', EP_NONE);
});

// Handle endpoint content display
add_action('template_redirect', function() {
    global $wp_query;

    // Trailer endpoint
    if (isset($wp_query->query_vars['trailer'])) {
        status_header(200);
        echo "<h1>🎬 Movie Trailer</h1>";
        echo "<p>Here is the trailer for this movie!</p>";
        exit;
    }

    // Behind-the-scenes endpoint
    if (isset($wp_query->query_vars['behind-the-scenes'])) {
        status_header(200);
        echo "<h1>🎥 Behind the Scenes</h1>";
        echo "<p>Exclusive BTS content for this movie!</p>";
        exit;
    }
});

// Flush rewrite rules on plugin activation
register_activation_hook(__FILE__, function() {
    // Register endpoints first
    add_rewrite_endpoint('trailer', EP_PERMALINK);
    add_rewrite_endpoint('behind-the-scenes', EP_NONE);
    flush_rewrite_rules();
});

// Custom rule for EP_NONE endpoint
add_action('init', function() {
    // Match: /movies/action/the-avengers/behind-the-scenes/
    add_rewrite_rule(
        '^movies/([^/]+)/([^/]+)/behind-the-scenes/?$',
        'index.php?post_type=movie&name=$matches[2]&behind-the-scenes=1',
        'top'
    );
});
