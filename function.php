//creating custom post type
function create_post_type() {
    register_post_type( 'custom_media',
        array(
            'labels' => array(
                'name' => __( 'Medias' ),
                'singular_name' => __( 'Medias' )
            ),
            'public' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'custom_media'),
            'supports' => array('title', 'editor', 'thumbnail')
        )
    );
}
add_action( 'init', 'create_post_type' );

//custom meta box
add_action( 'add_meta_boxes', 'cd_meta_box_add' );
function cd_meta_box_add()
{
    add_meta_box( 'my-meta-box-id', 'Media Image Box', 'show_your_fields_meta_box', 'Custom_Media', 'normal', 'high' );
}

function show_your_fields_meta_box() {
    global $post;
    $meta = get_post_meta( $post->ID, 'your_fields', true ); ?>

    <input type="hidden" name="your_meta_box_nonce" value="<?php echo wp_create_nonce( basename(__FILE__) ); ?>">
    <p>
        <label for="your_fields[image]">Image Upload</label><br>
        <input type="text" name="your_fields[image]" id="your_fields[image]" class="meta-image regular-text" value="<?php if (is_array($meta) && isset($meta['image'])){echo $meta['image'];} ?>">
        <input type="button" class="button image-upload" value="Browse">
    </p>

    <div class="image-preview"><img src="<?php if (is_array($meta) && isset($meta['image'])){ echo $meta['image'];} ?>" style="max-width: 250px;"></div>
    <!-- All fields will go here -->
    <script>
        jQuery(document).ready(function ($) {
            // Instantiates the variable that holds the media library frame.
            var meta_image_frame;
            // Runs when the image button is clicked.
            $('.image-upload').click(function (e) {
                // Get preview pane
                var meta_image_preview = $(this).parent().parent().children('.image-preview');
                // Prevents the default action from occuring.
                e.preventDefault();
                var meta_image = $(this).parent().children('.meta-image');
                // If the frame already exists, re-open it.
                if (meta_image_frame) {
                    meta_image_frame.open();
                    return;
                }
                // Sets up the media library frame
                meta_image_frame = wp.media.frames.meta_image_frame = wp.media({
                    title: meta_image.title,
                    button: {
                        text: meta_image.button
                    }
                });
                // Runs when an image is selected.
                meta_image_frame.on('select', function () {
                    // Grabs the attachment selection and creates a JSON representation of the model.
                    var media_attachment = meta_image_frame.state().get('selection').first().toJSON();
                    // Sends the attachment URL to our custom image input field.
                    meta_image.val(media_attachment.url);
                    meta_image_preview.children('img').attr('src', media_attachment.url);
                });
                // Opens the media library frame.
                meta_image_frame.open();
            });
        });
    </script>

<?php }

function save_your_fields_meta( $post_id ) {
    // verify nonce
    if ( isset($_POST['your_meta_box_nonce'])
        && !wp_verify_nonce( $_POST['your_meta_box_nonce'], basename(__FILE__) ) ) {
        return $post_id;
    }
    // check autosave
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return $post_id;
    }
    // check permissions
    if (isset($_POST['post_type'])) { //Fix 2
        if ( 'page' === $_POST['post_type'] ) {
            if ( !current_user_can( 'edit_page', $post_id ) ) {
                return $post_id;
            } elseif ( !current_user_can( 'edit_post', $post_id ) ) {
                return $post_id;
            }
        }
    }

    $old = get_post_meta( $post_id, 'your_fields', true );
    if (isset($_POST['your_fields'])) { //Fix 3
        $new = $_POST['your_fields'];
        if ( $new && $new !== $old ) {
            update_post_meta( $post_id, 'your_fields', $new );
        } elseif ( '' === $new && $old ) {
            delete_post_meta( $post_id, 'your_fields', $old );
        }
    }
}
add_action( 'save_post', 'save_your_fields_meta' );

//owl carousel
add_action( 'wp_enqueue_scripts', 'yourtheme_scripts');
function yourtheme_scripts(){
    wp_enqueue_script('owl.carousel', get_template_directory_uri() . '/assets/js/owl.carousel.min.js', array(), '1.0.0', true );
    wp_enqueue_script('settings', get_template_directory_uri() . '/assets/js/settings.js', array(), '1.0.0', true );
}

function yourtheme_styles() {
    wp_register_style('owl-carousel', get_template_directory_uri() .'/assets/css/owl.carousel.css', array(), null, 'all' );
    wp_enqueue_style( 'owl-carousel' );
    wp_register_style('owl-carousel-animate', get_template_directory_uri() .'/assets/css/animate.css', array(), null, 'all' );
    wp_enqueue_style( 'owl-carousel-animate' );
}
add_action( 'wp_enqueue_scripts', 'yourtheme_styles' );

//creating shortcode
function abc() {
    ?>
<!-- ============ BLOG CAROUSEL START ============ -->
<section id="blog">
  <div class="container">
     <div class="row"> <!-- this is the carousel’s header row -->
        <div class="col-sm-12 text-center">
           <h5>Recent posts</h5>
           <h1>Blog</h1>
        </div>
     </div>
     <div class="row"> <!-- this row starts the carousel-->
        <div class="col-sm-12">
           <div class="owl-carousel">
              <!-- query the posts to be displayed -->
              <?php $loop = new WP_Query(array('post_type' => 'Custom_Media', 'posts_per_page' => -1, 'orderby'=> 'ASC')); //Note, you can change the post_type to a CPT and set the number to pull and order to display them in here. ?>
              <?php while ( $loop->have_posts() ) : $loop->the_post(); ?>
                 <div class="recent-post"> <!-- I don’t have any unique css here, just needed to make sure every recent post gets wrapped as a block element.  However, you may want to add your own css here... -->
                     <?php $meta=get_post_meta( get_the_ID(), 'your_fields', true ); ?>
<a href="<?php print get_permalink($post->ID) ?>">
              <?php //echo the_post_thumbnail();  ?><img src="<?php echo $meta['image']; ?>"></a>
              <h4><?php print get_the_title(); ?></h4>
              <?php print get_the_excerpt(); ?><br />
          <p><a class="btn btn-default" href="<?php print get_permalink($post->ID) ?>">More</a></p>
</div> <!-- End the Recent Post div -->
              <?php endwhile; ?>
           </div> <!-- End the Owl Carousel div -->
        </div> <!-- End the recent posts carousel row -->
        <div class="row"> <!-- start of new row for the link to the blog -->
           <div class="col-sm-12 text-center">
              <a href="#" class="btn btn-primary">Read All Posts</a>
           </div>
        </div>
     </div> <!-- End blog button row -->
</section>  <!-- ============ RECENT POSTS CAROUSEL END ============ -->
    <script>
        (function($) {
            "use strict";

            $(document).ready(function() {

                $('#blog .owl-carousel').owlCarousel({
                    loop:true,
                    margin:10,
                    responsiveClass:true,
                    dots: false,
                    autoplay: true,
                    autoPlaySpeed: 5000,
                    autoPlayTimeout: 2000,
                    animateOut: 'slideOutDown',
                    animateIn: 'flipInX',
                    nav: true,
                    navText: ['Prev','Next'], //Note, if you are not using Font Awesome in your theme, you can change this to Previous & Next
                    responsive:{
                        0:{
                            items:1,
                            //nav:true
                        },
                        767:{
                            items:2,
                            //nav:false
                        },
                        1080:{
                            items:<?php echo get_option('owl_value'); ?>,
                            //nav:false
                        },

                    }
                });
            })
        })(jQuery);
        jQuery('.custom1').owlCarousel({
            animateOut: 'slideOutDown',
            animateIn: 'flipInX',
            items:1,
            margin:30,
            stagePadding:30,
            smartSpeed:450
        });
    </script>
    <?php
}
function owl_func( $atts ){
    return abc() ;
}
add_shortcode( 'owl_shortcode', 'owl_func' );