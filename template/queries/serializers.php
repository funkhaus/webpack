<?php

    function serializer_image( $attachment = null ){

        if ( !$attachment ) return false;

        $output = array();

        $attachment = get_post($attachment);
        $sizes = get_intermediate_image_sizes();

        $output['title'] = get_the_title($attachment->ID);

        // add image colors if FIC is installed
        if ( function_exists('get_primary_image_color') )
            $output['primary_color'] = get_primary_image_color($attachment->ID);

        if ( function_exists('get_second_image_color') )
            $output['secondary_color'] = get_second_image_color($attachment->ID);

        // build out sizes
        foreach ( $sizes as $size ){
            $img_data = wp_get_attachment_image_src($attachment->ID, $size);

            $output['sizes'][$size] = array(
                'url'       => $img_data[0],
                'width'     => $img_data[1],
                'height'    => $img_data[2],
                'html'      => wp_get_attachment_image($attachment->ID, $size)
            );
        }

        return $output;
    }

    // Define all serializers here

    function serializer_standard( $target_post = null ){

        $target_post = get_post( $target_post );

        if( ! $target_post ){
            return '';
        }

        // Get all meta fields without leading underscore
        $meta = get_post_meta( $target_post->ID, '', true );
        $filtered_meta = array_filter( $meta, 'filter_leading_underscore', ARRAY_FILTER_USE_KEY );

        $output = array(
            'id'            => $target_post->ID,
            'title'         => get_the_title($target_post),
            'content'       => apply_filters('the_content', $target_post->post_content),
            'excerpt'       => get_the_excerpt($target_post),
            'permalink'     => get_permalink($target_post),
            'slug'          => $target_post->post_name,
            'relativePath'  => remove_siteurl( $target_post ),
            'featuredImage' => serializer_image( get_post_thumbnail_id( $target_post->ID ) ),
            'meta'          => array_map( 'reset', $filtered_meta ),
            'date'          => get_the_date('U', $target_post->ID)
        );

        return $output;
    }

    // Serializes all menu items, given menu slug/name/ID
    function serializer_menu( $menu_name ){

        $fetched = wp_get_nav_menu_items( $menu_name );

        // Exit if menu not found
        if( ! $fetched ) return false;

        // Format menu items
        $formatted_items = array_map( 'serializer_menu_item', $fetched );

        // Save menu object
        $menu_object = wp_get_nav_menu_object( $menu_name );

        $output = array(
            'name'          => $menu_object->name,
            'slug'          => $menu_object->slug,
            'items'         => $formatted_items
        );

        return $output;

    }

    function serializer_menu_item( $item ){

        $target_id = (int)$item->object_id;

        $relative_path = remove_siteurl( $target_id );

        // Make sure we get at least an slash from the relative path
        if( ! $relative_path ){
            $relative_path = '/';
        }

        $output = array(
            'title'         => $item->title,
            'classes'       => $item->classes,
            'permalink'     => $item->url,
            'relativePath'  => $relative_path,
            'is_external'   => $item->type_label == 'Custom Link'
        );

        return $output;
    }

    // Used on array_filter to remove $key with leading underscore ( _fails )
    function filter_leading_underscore( $key ){
        return ! preg_match('/^_/', $key);
    }

    // Removes site url to retrieve relative path
    function remove_siteurl( $target_post ){
        $replaced = str_replace( get_option('siteurl'), '', get_the_permalink( $target_post ) );

        return rtrim( $replaced, '/' );
    }
