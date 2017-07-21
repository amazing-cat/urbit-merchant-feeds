<?php

function urberProductfeedCacheField( $val ) {
    $config = get_option( URBER_PRODUCTFEED_CONFIG );
    ?>
    <input
        type="number"
        name="<?= URBER_PRODUCTFEED_CONFIG ?>[cache]"
        value="<?= esc_attr( $config['cache'] ) ?>"
    />
    <?php
}

function urberProductfeedCategoriesField( $val ) {
    $categories = $val['categories'];
    $size = $val['size'];
    $config = get_option(URBER_PRODUCTFEED_CONFIG);
    ?>
    <select multiple="multiple" size="<?= $size ?>" name="<?= URBER_PRODUCTFEED_CONFIG ?>[filter][categories][]">
    <?php foreach ($categories as $category) {
        printf(
            '<option value="%s" %s>%s</option>',
            $category->term_id,
            in_array( $category->term_id, $config['filter']['categories']) ? 'selected="selected"' : '',
            $category->cat_name
        );
    }
    ?>
    </select>
    <?php
}

function urberProductfeedTagsField( $val ){
    $tags = $val['tags'];
    $size = $val['size'];
    $config = get_option(URBER_PRODUCTFEED_CONFIG);
    ?>
    <select multiple="multiple" size="<?= $size ?>" name="<?= URBER_PRODUCTFEED_CONFIG ?>[filter][tags][]">
    <?php foreach ($tags as $tag) {
        printf(
            '<option value="%s" %s>%s</option>',
            $tag->term_id,
            in_array( $tag->term_id, $config['filter']['tags']) ? 'selected="selected"' : '',
            $tag->name
        );
    }
    ?>
    </select>
    <?php
}

function urberProductfeedStockField() {
    $config = get_option( URBER_PRODUCTFEED_CONFIG );
    ?>
    <input
        type="number"
        name="<?= URBER_PRODUCTFEED_CONFIG ?>[stock]"
        value="<?= esc_attr( $config['stock'] ) ?>"
    />
    <?php
}

function urberProductfeedDimensionsField() {
    echo 'get me attrs';
}