<div class="wrap">
    <h2><?php echo get_admin_page_title() ?></h2>

    <form action="options.php" method="POST">
        <?php
            settings_fields( 'productfeed_group' );
            do_settings_sections( 'feed_cache' );
            do_settings_sections( 'product_filter' );
            do_settings_sections( 'product_fields' );
            submit_button();
        ?>
    </form>
</div>