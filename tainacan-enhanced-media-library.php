<?php
/**
 * Plugin Name: Tainacan: Enhanced Media Library
 * Plugin URI: http://mypluginuri.com/
 * Description: Enhence WordPress' media library showing images used in a Tainacan collection
 * Version: 1.0
 * Author: André Alvim
 * Author URI: Author's website
 * License: A "Slug" license name e.g. GPL12
 */
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

$plug_in_dir = plugin_dir_url(__FILE__);

/*
 ***************************************** Adição de arquivos externos*********************************************************************************
 */
//Adição de estilos
wp_enqueue_style("bootstrap_css", $plug_in_dir . "css/bootstrap.min.css", null, false, "all");
wp_enqueue_style("bootstrap_theme_css", $plug_in_dir . "css/bootstrap-theme.min.css", array("boootstrap_css"), false, "all");
wp_enqueue_style("style_css", $plug_in_dir . "css/style.css", null, false, "all");
wp_enqueue_style( "wpb-fa", 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css' );

//Adição de scripts
wp_enqueue_script("JQuery", $plug_in_dir . 'js/jquery-3.2.0.min.js', null, "3.2.0", false);
wp_enqueue_script("bootstrap_js", $plug_in_dir . 'js/bootstrap.min.js', array('JQuery'), false, false);
wp_enqueue_script("main_js", $plug_in_dir . 'js/main.js', array('JQuery'), false, false);

/*
 ******************************************** Actions and filters ***************************************************************************************
 */

// Adiciona uma nova aba ao "Media Library"
add_filter('media_upload_tabs', 'upload_tab');
function upload_tab($tabs) {
    $tabs['tabname'] = __("Collections", "enhenced-library");
    return $tabs;
}

//Adiciona conteudo a aba criada
add_action('media_upload_tabname', 'add_content');
function add_content() {
    wp_iframe( 'get_content' );
}

/*
 ****************************************************** Switch Operation **********************************************************************************
 */

$operation = $_POST['operation'];
if(isset($operation))
{
    $data = $_POST['data'];
    $posts = get_posts(array('post_type' => 'socialdb_collection'));

    switch ($operation)
    {
        case 'wichCollection':
            $wichCollection = $data;
            if($wichCollection != "all")
            {
                foreach ($posts as $post)
                {
                    if($post->ID == $wichCollection)
                    {
                        $has_images = work_on_itens($post);
                        if(!$has_images)
                        {
                            ?>
                            <div class="text-center ">
                                <h1><?php _e("There aren't images!", "enhenced-library"); ?></h1>
                            </div>
                            <?php
                        }
                    }
                }
            }
            else
            {
                $has_images = work_on_itens($posts);
                if(!$has_images)
                {
                    ?>
                    <div class="text-center ">
                        <h1><?php _e("There aren't images!", "enhenced-library"); ?></h1>
                    </div>
                    <?php
                }
            }
            break;
        case 'search':
            $search_term = $data;
            $collectionToSearch = $_POST['collectionToSearch'];
            do_search($search_term, $collectionToSearch);
            break;
        case 'backHome':
            $wichCollection = $_POST['wichCollection'];
            if($wichCollection != "all")
            {
                foreach ($posts as $post)
                {
                    if($post->ID == $wichCollection)
                    {
                        work_on_itens($post);
                        break;
                    }

                }
            }
            else
            {
                work_on_itens($posts);
            }
            break;
    }
}

/*
 ****************************************************** Funções ******************************************************************************************
 */

//Adiciona conteúdo a aba INICIO
function get_content() {
    echo media_upload_header();// This function is used for print media uploader headers etc.

    $posts = get_posts(array('post_type' => 'socialdb_collection'));

    ?>
    <div style="padding: 0 0 35px 0 ;">
        <div class="input-group col-xs-12" id="search_bar_container">
            <div class="col-xs-4">
                <select class="form-control" id="wichCollection" name="wichCollection">
                        <option value="all" selected><?php _e("All collections", "enhenced-library"); ?></option>
                        <?php
                            foreach ($posts as $post)
                            {
                                ?>
                                <option value="<?php echo $post->ID ?>"><?php echo $post->post_title ?></option>
                                <?php
                            }
                         ?>
                </select>
            </div>
            <div class="input-group col-xs-8">
                <input class="form-control" type="text" id="search_bar">
                <span class="input-group-btn">
                    <button type="button" class="btn btn-primary" id="search" disabled>
                        <?php _e("Search", "enhenced-library"); ?> <i class="fa fa-search" aria-hidden="true"></i>
                    </button>
                </span>
            </div>
        </div>
        <div id="backContainer">
            <button type="button" id="backHome" class="btn btn-primary pull-left" disabled><i class="fa fa-home" aria-hidden="true"></i> <?php _e("Collection home", "enhenced-library"); ?></button>
        </div>
    </div>

    <!-- File Path -->
    <input type="hidden" value="<?php echo plugin_dir_url(__FILE__); ?>" id="location">

    <div class="add_padding">
        <div class="col-md-12" id="main_div_up">
            <div class="container-fluid" id="main_div">
    <?php

    $has_images = work_on_itens($posts);
    if(!$has_images)
    {
        ?>
        <div class="text-center ">
            <h1>Não há imagens</h1>
        </div>
        <?php
    }

    ?>
            </div>
        </div>
    </div>

    <div id="bottom-bar">
        <button type="button" id="addImages" class="btn btn-primary pull-right" disabled><?php _e("Add", "enhenced-library"); ?></button>
    </div>
    <?php

}

function work_on_itens($posts)
{
    $qtd_itens = 0;
    $has_images = false;
    ?>
    <div class="row">
    <?php
    if(count($posts) >= 2)
    {
        foreach($posts as $post) {
            $collections_itens = get_collection_posts($post->ID);
            $return = generate_itens($collections_itens, $qtd_itens);
            if($return)
            {
                $has_images = true;
            }
        }
    }else if(count($posts) == 1)
    {
        $collections_itens = get_collection_posts($posts->ID);

        $return = generate_itens($collections_itens, $qtd_itens);

        if($return)
        {
            $has_images = true;
        }
    }

    ?>
    </div>
    <?php

    return $has_images;
}

function generate_itens($collections_itens, &$qtd_itens)
{
    $has_images = false;
    foreach ($collections_itens as $item) {
        $return = generate_one_item($item, $qtd_itens);
        if($return)
        {
            $has_images = true;
        }
    }

    return $has_images;
}

function generate_one_item($item, &$qtd_itens)
{
    $has_images = false;
    //Get item thumbnail
    $itens_thumbnail_url = get_the_post_thumbnail_url($item->ID);
    if ($itens_thumbnail_url)
    {
        ?>
        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
            <figure class="thumbnail" onclick="$(this).toggleClass('clicked_image')">
                <img src="<?php echo $itens_thumbnail_url ?>" style="height: 200px; ">
            </figure>
        </div>
        <?php
        $qtd_itens++;
        if($qtd_itens == 4)
        {
            ?>
            </div>
            <div class="row">
            <?php
            $qtd_itens = 0;
        }

        $has_images = true;
    }

    //Get attatchments
    $data['object_id'] = $item->ID;
    $files = show_files($data);
    if ($files) {
        foreach ($files['image'] as $index => $file) {
            ?>
            <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3" >
                <figure class="thumbnail" onclick="$(this).toggleClass('clicked_image')">
                    <img src="<?php echo $file->guid ?>" style="height: 200px;" >
                </figure>
            </div>
            <?php

            $qtd_itens++;
            if($qtd_itens == 4)
            {
                ?>
                </div>
                <div class="row">
                <?php
                $qtd_itens = 0;
            }
        }

        $has_images = true;
    }

    return $has_images;
}
//Adiciona conteúdo a aba FIM

//Realiza pesquisa
function do_search($search_term, $collectionToSearch)
{
    $all_posts = get_posts(array('post_type' => 'socialdb_collection'));
    $search_term = strtolower($search_term);
    $qtd_itens = 0;
    $has_images = false;

    if($collectionToSearch == "all")
    {
        foreach ($all_posts as $post)
        {
            $found = false;
            //Collection Search
            foreach ($post as $post_meta_value)
            {
                //If a metadata be found in any $post_meta_value so loop stop and it's showed all itens from this collection
                $post_meta_value = strtolower($post_meta_value);
                if(approximate_search($search_term, $post_meta_value))
                {
                    $found = true;
                    break;
                }
            }

            if($found)
            {
                $return = work_on_itens($post);
                if(!$has_images)
                {
                    $has_images = $return;
                }
            }else
            {
                $return = searchInChildren($post->ID, $search_term);
                if(!$has_images)
                {
                    $has_images = $return;
                }
            }
        }
    }
    else
    {
        $has_images = searchInChildren($collectionToSearch, $search_term);
    }

    if(!$has_images)
    {
        ?>
            <div class="text-center">
                <h1><?php _e("No results returned", "enhenced-library"); ?></h1>
            </div>
        <?php
    }
}

//Realiza busca em item de uma coleção
function searchInChildren($postId, $searchTerm)
{
    $has_images = false;
    $collection_itens = get_collection_posts($postId);
    $category_root_id = get_post_meta($postId, 'socialdb_collection_object_type', true);
    $properties = get_term_meta($category_root_id,'socialdb_category_property_id');
    $properties = array_unique($properties);

    foreach ($properties as $property)
    {
        if($property)
        {
            foreach ($collection_itens as $item)
            {
                $meta_value = get_post_meta($item->ID, "socialdb_property_".$property);

                $meta_value[0] = strtolower($meta_value[0]);
                if(approximate_search($searchTerm, $meta_value[0])  || approximate_search($searchTerm, strtolower($item->post_title)))
                {
                    $return = generate_one_item($item, $qtd_itens);
                    if($return)
                    {
                        $has_images = true;
                    }
                }
            }
        }

    }

    return $has_images;
}

//Verifica existencia de uma taxonomia
function library_term_exists($term, $taxonomy) {
    global $wpdb;
    $sql = sprintf("select t.term_id, t.slug, t.name, tt.term_taxonomy_id from %s tt inner join %s t on t.term_id = tt.term_id where t.term_id = %s ", $wpdb->term_taxonomy, $wpdb->terms, $term);
    return $wpdb->get_row($sql, ARRAY_A);
}

//Busca os itens pertencentes a uma coleção
function get_collection_posts($collection_id, $field = '*', $post_status = 'publish') {
    global $wpdb;
    $wp_posts = $wpdb->prefix . "posts";
    $term_relationships = $wpdb->prefix . "term_relationships";
    $category_root_id = get_post_meta($collection_id, 'socialdb_collection_object_type', true);
    //$term = get_term_by('id', $category_root_id, 'socialdb_category_type');
    $term = library_term_exists($category_root_id, "socialdb_category_type");
    if ($term['term_taxonomy_id'] != null) {
        $query = "
                    SELECT p.$field FROM $wp_posts p
                    INNER JOIN $term_relationships t ON p.ID = t.object_id    
                    WHERE p.post_type LIKE 'socialdb_object' and t.term_taxonomy_id = {$term['term_taxonomy_id']} AND p.post_status LIKE '$post_status'
            ";

        $result = $wpdb->get_results($query);
        if ($result && is_array($result) && count($result) > 0) {
            return $result;
        } else {
            return array();
        }
    }
}

//Busca os anexos pertencentes a um item
function show_files($data) {
    $real_attachments = [];
    if ($data['object_id']) {
        $post = get_post($data['object_id']);
        $result = '';
        if (!is_object(get_post_thumbnail_id())) {
            $args = array(
                'post_type' => 'attachment',
                'numberposts' => -1,
                'post_status' => null,
                'post_parent' => $post->ID,
                'exclude' => get_post_thumbnail_id()
            );

            $attachments = get_posts($args);
            $arquivos = get_post_meta($post->ID, '_file_id');
            $object_content = get_post_meta($data['object_id'],'socialdb_object_content',true);
            if ($attachments) {
                foreach ($attachments as $attachment) {
                    if (in_array($attachment->ID, $arquivos)&&$object_content!=$attachment->ID) {
                        $metas = wp_get_attachment_metadata($attachment->ID);
                        $real_attachments['posts'][] = $attachment;
                        $extension = $attachment->guid;
                        $ext = pathinfo($extension, PATHINFO_EXTENSION);
                        if(in_array($ext, ['mp4','m4v','wmv','avi','mpg','ogv','3gp','3g2'])){
                            $real_attachments['videos'][] = $attachment;
                        }elseif (in_array($ext, ['jpg','jpeg','png','gif'])) {
                            $obj['metas'] = $metas;
                            $real_attachments['image'][] = $attachment;
                        }elseif (in_array($ext, ['mp3','m4a','ogg','wav','wma'])) {
                            $real_attachments['audio'][] = $attachment;
                        }elseif(in_array($ext, ['pdf'])){
                            $real_attachments['pdf'][] = $attachment;
                        }else{
                            $real_attachments['others'][] = $attachment;
                        }
                    }
                }
            }
        }
    }
    if(!empty($real_attachments)){
        return $real_attachments;
    }else{
        return false;
    }
}

//Retorna os filtros salvos
function get_saved_facets($collection_id) {
    $default_tree_orientation = get_post_meta($collection_id, 'socialdb_collection_facet_widget_tree_orientation', true);
    $default_tree_orientation = ($default_tree_orientation != '' ? $default_tree_orientation : 'left-column');
    $facets_id = array_filter(array_unique(get_post_meta($collection_id, 'socialdb_collection_facets')));
    $arrFacets = array();
    //$prop = new PropertyModel();

    foreach ($facets_id as $facet_id) {
        $facet['id'] = $facet_id;
        $facet['widget'] = get_post_meta($collection_id, 'socialdb_collection_facet_' . $facet_id . '_widget', true);
        if(has_filter('get_filter_name') && apply_filters('get_filter_name', $facet['id']) ){
            $facet['nome'] = apply_filters('get_filter_name', $facet['id']);
            $facet['orientation'] = $default_tree_orientation;
        }

        $facet_property = library_term_exists($facet['id'], 'socialdb_property_type');
        $facet['prop'] = $facet_property['name'];
        //buscando os dados de cada tipo
        if ($facet['id'] == 'tag' || ($facet_property['slug'] && $facet_property['slug'] == 'socialdb_property_fixed_tags') ) {
            $facet['id'] = 'tag';
            $facet['nome'] = 'Tag';
            //$facet['widget'] = 'tree';
            $facet['orientation'] = $default_tree_orientation;
        }else if ($facet['id'] == 'ranking_colaborations') {
            $facet['name'] = __('Colaboration Ranking','tainacan');
            $facet['orientation'] = $default_tree_orientation;
        }else if ($facet['id'] == 'notifications') {
            $facet['name'] = __('Notifications','tainacan');
            $facet['orientation'] = $default_tree_orientation;
        }else if ($facet['id'] == 'socialdb_object_from') {
            $facet['name'] = __('Format','tainacan');
            $facet['widget'] = 'tree';
            $facet['orientation'] = $default_tree_orientation;
        }else if ($facet['id'] == 'socialdb_object_dc_type') {
            $facet['name'] = __('Type','tainacan');
            $facet['widget'] = 'tree';
            $facet['orientation'] = $default_tree_orientation;
        }else if ($facet['id'] == 'socialdb_object_dc_source') {
            $facet['name'] = __('Source','tainacan');
            $facet['widget'] = 'tree';
            $facet['orientation'] = $default_tree_orientation;
        } else if ($facet['id'] == 'socialdb_license_id') {
            $facet['name'] = __('License','tainacan');
            $facet['widget'] = 'tree';
            $facet['orientation'] = $default_tree_orientation;
        } else {
            $property = library_term_exists($facet['id'], "socialdb_property_type");
            if ($facet['widget'] == 'tree') {
                $facet['orientation'] = $default_tree_orientation;
                $facet['name'] = $property['name'];
                $property = get_term_by('id', $facet['id'], 'socialdb_category_type');
                if($property){
                    $facet['name'] = $property['name'];
                }
            } else if( $facet['widget'] == 'menu' ) {
                $property = get_term_by('id', $facet['id'], 'socialdb_category_type');
                if($property){
                    $facet['name'] = $property['name'];
                }
                $facet['orientation'] = $default_tree_orientation;
            } else {
                $facet['orientation'] = get_post_meta($collection_id, 'socialdb_collection_facet_' . $facet['id'] . '_orientation', true);
                if ($property) {
                    $facet['name'] = $property['name'];
                } elseif(is_numeric($facet['id'])) {
                    $category = library_term_exists($facet['id'], "socialdb_property_type");
                    $facet['name'] = $category['name'];
                }
            }
        }

        $facet['priority'] = get_post_meta($collection_id, 'socialdb_collection_facet_' . $facet_id . '_priority', true);

        $arrFacets[] = $facet;
    }

    usort($arrFacets, 'compare_priority'); // sort by priority

    return $arrFacets;
}

//Retorna tipo de propriedade
function get_property_type($property_id) {
    $parent_id = get_term_by('id', $property_id, 'socialdb_property_type')->parent;
    $parent = get_term_by('id', $parent_id, 'socialdb_property_type');
    return $parent->name;
}

//Realiza busca aproximada
function approximate_search($search_term, $haystack)
{
    return preg_match("/(".$search_term.")/", $haystack);
}