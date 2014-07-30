<?php
  require('includes/application_top.php');
//comments
/* the following cPath references come from application_top.php  */

  $category_depth = 'top';

  if (isset($cPath) && tep_not_null($cPath)) {

    $categories_products_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_TO_CATEGORIES . " where categories_id = '" . (int)$current_category_id . "'");

    $categories_products = tep_db_fetch_array($categories_products_query);

    if ($categories_products['total'] > 0) {

      $category_depth = 'products'; /* display products */

    } else {

      $category_parent_query = tep_db_query("select count(*) as total from " . TABLE_CATEGORIES . " where parent_id = '" . (int)$current_category_id . "'");

      $category_parent = tep_db_fetch_array($category_parent_query);

      if ($category_parent['total'] > 0) {

        $category_depth = 'nested'; /* navigate through the categories  */

      } else {

        $category_depth = 'products'; /* category has no products, but display the 'no products' message  */

      }

    }

  }

  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_DEFAULT);


require(DIR_WS_INCLUDES . 'template_top.php');

    if ($category_depth == 'nested') {
    /*** Begin Header Tags SEO ***/
    $category_query = tep_db_query("select cd.categories_name, c.categories_image, cd.categories_htc_title_tag, cd.categories_htc_description from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id = '" . (int)$current_category_id . "' and cd.categories_id = '" . (int)$current_category_id . "' and cd.language_id = '" . (int)$languages_id . "'");
    /*** end Header Tags SEO ***/
    $category = tep_db_fetch_array($category_query);


/* main category image */

if (isset($category['categories_image'])) {
	echo '<div class="category_image">'.tep_image(DIR_WS_IMAGES . $category['categories_image'], $category['categories_name'], '','', 'class="scale-with-grid"').'</div>'; 
}

?>
	

<h1><?php echo $category['categories_htc_title_tag']; ?></h1>

  <div class="contentContainer">

  <div class="contentText">

    <div class="subcategory_name_wrapper">

<?php

	if (isset($cPath) && strpos($cPath,'_')) {

        /* check to see if there are deeper categories within the current category   */

      $category_links = array_reverse($cPath_array);

      for($i=0, $n=sizeof($category_links); $i<$n; $i++) {

        $categories_query = tep_db_query("select count(*) as total from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.parent_id = '" . (int)$category_links[$i] . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "'");

        $categories = tep_db_fetch_array($categories_query);

        if ($categories['total'] < 1) {

            /* do nothing, go through the loop  */

        } else {

          $categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.categories_image, c.parent_id from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.parent_id = '" . (int)$category_links[$i] . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' order by sort_order, cd.categories_name");

          break; /* we've found the deepest category the customer is in   */

        }

      }

    } else {

      $categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.categories_image, c.parent_id from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.parent_id = '" . (int)$current_category_id . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' order by sort_order, cd.categories_name");

    }



    $number_of_categories = tep_db_num_rows($categories_query);



    $rows = 0;

    while ($categories = tep_db_fetch_array($categories_query)) {

      $rows++;

      $cPath_new = tep_get_path($categories['categories_id']);

      $width = (int)(100 / MAX_DISPLAY_CATEGORIES_PER_ROW) . '%';
		  echo '<div class="subcategory_name">
		  <a href="' . tep_href_link(FILENAME_DEFAULT, $cPath_new) . '">' . $categories['categories_name'] . '
		  </a>
		  </div>' . "\n";
    }
        /* needed for the new products module shown below    */
    $new_products_category_id = $current_category_id;
?>
  </div>  

<?php include(DIR_WS_MODULES . FILENAME_NEW_PRODUCTS); ?>
  </div>
    <?php 
/*** Begin Header Tags SEO ***/ 
if (tep_not_null($category['categories_htc_description'])) { 
   echo '<div class="clear"></div><center><h4 style="text-decoration:none;">' . $category['categories_htc_description'] . '</h4></center>';
} 
/*** End Header Tags SEO ***/ 
?>
</div>
<?php

  } elseif ($category_depth == 'products' || (isset($HTTP_GET_VARS['manufacturers_id']) && !empty($HTTP_GET_VARS['manufacturers_id']))) {

      /* create column list   */

    $define_list = array('PRODUCT_LIST_MODEL' => PRODUCT_LIST_MODEL,
                         'PRODUCT_LIST_NAME' => PRODUCT_LIST_NAME,
                         'PRODUCT_LIST_MANUFACTURER' => PRODUCT_LIST_MANUFACTURER,
                         'PRODUCT_LIST_PRICE' => PRODUCT_LIST_PRICE,
                         'PRODUCT_LIST_QUANTITY' => PRODUCT_LIST_QUANTITY,
                         'PRODUCT_LIST_WEIGHT' => PRODUCT_LIST_WEIGHT,
                         'PRODUCT_LIST_IMAGE' => PRODUCT_LIST_IMAGE,
                         'PRODUCT_LIST_BUY_NOW' => PRODUCT_LIST_BUY_NOW);
    asort($define_list);
    $column_list = array();
    reset($define_list);
    while (list($key, $value) = each($define_list)) {
      if ($value > 0) $column_list[] = $key;
    }
    $select_column_list = '';

    for ($i=0, $n=sizeof($column_list); $i<$n; $i++) {
      switch ($column_list[$i]) {
        case 'PRODUCT_LIST_MODEL':
          $select_column_list .= 'p.products_model, ';
          break;
        case 'PRODUCT_LIST_NAME':
          $select_column_list .= 'pd.products_name, ';
          break;
        case 'PRODUCT_LIST_MANUFACTURER':
          $select_column_list .= 'm.manufacturers_name, ';
          break;
        case 'PRODUCT_LIST_QUANTITY':
          $select_column_list .= 'p.products_quantity, ';
          break;
        case 'PRODUCT_LIST_IMAGE':
          $select_column_list .= 'p.products_image, ';
          break;
        case 'PRODUCT_LIST_WEIGHT':
          $select_column_list .= 'p.products_weight, ';
          break;
      }
    }
      /* show the products of a specified manufacturer    */

    if (isset($HTTP_GET_VARS['manufacturers_id']) && !empty($HTTP_GET_VARS['manufacturers_id'])) {

      if (isset($HTTP_GET_VARS['filter_id']) && tep_not_null($HTTP_GET_VARS['filter_id'])) {

          /* We are asked to show only a specific category  */

        $listing_sql = "select " . $select_column_list . " p.products_id, p.manufacturers_id, p.products_price, p.products_date_available, p.products_tax_class_id, IF(s.status, s.specials_new_products_price, NULL) as specials_new_products_price, IF(s.status, s.specials_new_products_price, p.products_price) as final_price, m.manufacturers_name as manu from " . TABLE_PRODUCTS . " p left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_MANUFACTURERS . " m, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_status = '1' and p.manufacturers_id = m.manufacturers_id and m.manufacturers_id = '" . (int)$HTTP_GET_VARS['manufacturers_id'] . "' and p.products_id = p2c.products_id and pd.products_id = p2c.products_id and pd.language_id = '" . (int)$languages_id . "' and p2c.categories_id = '" . (int)$HTTP_GET_VARS['filter_id'] . "'";

      } else {

          /* We show them all */

        $listing_sql = "select " . $select_column_list . " p.products_id, p.manufacturers_id, p.products_price, p.products_date_available,  p.products_tax_class_id, IF(s.status, s.specials_new_products_price, NULL) as specials_new_products_price, IF(s.status, s.specials_new_products_price, p.products_price) as final_price, m.manufacturers_name as manu from " . TABLE_PRODUCTS . " p left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_MANUFACTURERS . " m where p.products_status = '1' and pd.products_id = p.products_id and pd.language_id = '" . (int)$languages_id . "' and p.manufacturers_id = m.manufacturers_id and m.manufacturers_id = '" . (int)$HTTP_GET_VARS['manufacturers_id'] . "'";

      }

    } else {

        /* show the products in a given category  */

      if (isset($HTTP_GET_VARS['filter_id']) && tep_not_null($HTTP_GET_VARS['filter_id'])) {

          /* We are asked to show only specific category  */

        $listing_sql = "select " . $select_column_list . " p.products_id, p.manufacturers_id, p.products_price, p.products_date_available,  p.products_tax_class_id, IF(s.status, s.specials_new_products_price, NULL) as specials_new_products_price, IF(s.status, s.specials_new_products_price, p.products_price) as final_price, m.manufacturers_name as manu from " . TABLE_PRODUCTS . " p left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_MANUFACTURERS . " m, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_status = '1' and p.manufacturers_id = m.manufacturers_id and m.manufacturers_id = '" . (int)$HTTP_GET_VARS['filter_id'] . "' and p.products_id = p2c.products_id and pd.products_id = p2c.products_id and pd.language_id = '" . (int)$languages_id . "' and p2c.categories_id = '" . (int)$current_category_id . "'";

      } else {

          /* We show them all     */

        $listing_sql = "select " . $select_column_list . " p.products_id, p.manufacturers_id, p.products_price, p.products_date_available, p.products_tax_class_id, IF(s.status, s.specials_new_products_price, NULL) as specials_new_products_price, IF(s.status, s.specials_new_products_price, p.products_price) as final_price, m.manufacturers_name as manu from " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS . " p left join " . TABLE_MANUFACTURERS . " m on p.manufacturers_id = m.manufacturers_id left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_status = '1' and p.products_id = p2c.products_id and pd.products_id = p2c.products_id and pd.language_id = '" . (int)$languages_id . "' and p2c.categories_id = '" . (int)$current_category_id . "'";

      }

    }



    if ( (!isset($HTTP_GET_VARS['sort'])) || (!preg_match('/^[1-8][ad]$/', $HTTP_GET_VARS['sort'])) || (substr($HTTP_GET_VARS['sort'], 0, 1) > sizeof($column_list)) ) {

      for ($i=0, $n=sizeof($column_list); $i<$n; $i++) {

        if ($column_list[$i] == 'PRODUCT_LIST_NAME') {

          $HTTP_GET_VARS['sort'] = $i+1 . 'a';

          $listing_sql .= " order by pd.products_name";

          break;
        }
      }
    } else {
      $sort_col = substr($HTTP_GET_VARS['sort'], 0 , 1);
      $sort_order = substr($HTTP_GET_VARS['sort'], 1);
      switch ($column_list[$sort_col-1]) {

        case 'PRODUCT_LIST_MODEL':
          $listing_sql .= " order by p.products_model " . ($sort_order == 'd' ? 'desc' : '') . ", pd.products_name";
          break;
        case 'PRODUCT_LIST_NAME':
          $listing_sql .= " order by pd.products_name " . ($sort_order == 'd' ? 'desc' : '');
          break;
        case 'PRODUCT_LIST_MANUFACTURER':
          $listing_sql .= " order by m.manufacturers_name " . ($sort_order == 'd' ? 'desc' : '') . ", pd.products_name";
          break;
        case 'PRODUCT_LIST_QUANTITY':
          $listing_sql .= " order by p.products_quantity " . ($sort_order == 'd' ? 'desc' : '') . ", pd.products_name";
          break;
        case 'PRODUCT_LIST_IMAGE':
          $listing_sql .= " order by pd.products_name";
          break;
        case 'PRODUCT_LIST_WEIGHT':
          $listing_sql .= " order by p.products_weight " . ($sort_order == 'd' ? 'desc' : '') . ", pd.products_name";
          break;
        case 'PRODUCT_LIST_PRICE':
          $listing_sql .= " order by final_price " . ($sort_order == 'd' ? 'desc' : '') . ", pd.products_name";
          break;
      }
    }
    /*** Begin Header Tags SEO ***/
    if (isset($HTTP_GET_VARS['manufacturers_id'])) {
      $image = tep_db_query("select manufacturers_image, manufacturers_name as catname from " . TABLE_MANUFACTURERS . " where manufacturers_id = '" . (int)$HTTP_GET_VARS['manufacturers_id'] . "'");
      $image = tep_db_fetch_array($image);
      $db_query = tep_db_query("select manufacturers_htc_title_tag as htc_title, manufacturers_htc_description as htc_description from " . TABLE_MANUFACTURERS_INFO . " where languages_id = '" . (int)$languages_id . "' and manufacturers_id = '" . (int)$HTTP_GET_VARS['manufacturers_id'] . "'");
    } elseif ($current_category_id) {
      $image = tep_db_query("select c.categories_image, cd.categories_name as catname from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id = '" . (int)$current_category_id . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "'");
      $image = tep_db_fetch_array($image);
      $db_query = tep_db_query("select categories_htc_title_tag as htc_title, categories_htc_description as htc_description from " . TABLE_CATEGORIES_DESCRIPTION . " where categories_id = '" . (int)$current_category_id . "' and language_id = '" . (int)$languages_id . "'");
    }
    $htc = tep_db_fetch_array($db_query);
?>
<!-- subcategory view -->
<?php 
if (isset($image['categories_image'])) {
	echo '<div class="subcategory_image">'.tep_image(DIR_WS_IMAGES . $image['categories_image'], $image['catname'], '','', ' class="scale-with-grid"').'</div>'; 
}
?>
<h1><?php echo $htc['htc_title']; ?></h1>
    <?php /*** End Header Tags SEO ***/ ?>
<!-- subcategory view -->

  <div class="contentContainer">
<?php

      /* optional Product List Filter  */

    if (PRODUCT_LIST_FILTER > 0) {

      if (isset($HTTP_GET_VARS['manufacturers_id']) && !empty($HTTP_GET_VARS['manufacturers_id'])) {

        $filterlist_sql = "select distinct c.categories_id as id, cd.categories_name as name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where p.products_status = '1' and p.products_id = p2c.products_id and p2c.categories_id = c.categories_id and p2c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' and p.manufacturers_id = '" . (int)$HTTP_GET_VARS['manufacturers_id'] . "' order by cd.categories_name";

      } else {

        $filterlist_sql= "select distinct m.manufacturers_id as id, m.manufacturers_name as name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_MANUFACTURERS . " m where p.products_status = '1' and p.manufacturers_id = m.manufacturers_id and p.products_id = p2c.products_id and p2c.categories_id = '" . (int)$current_category_id . "' order by m.manufacturers_name";

      }

      $filterlist_query = tep_db_query($filterlist_sql);

      if (tep_db_num_rows($filterlist_query) > 1) {

        echo '<div class="form_1">' . tep_draw_form('filter', tep_href_link( FILENAME_DEFAULT ), 'get') . '<p class="t_right">' . TEXT_SHOW . '&nbsp;';

        if (isset($HTTP_GET_VARS['manufacturers_id'])&& !empty($HTTP_GET_VARS['manufacturers_id'])) {

          echo tep_draw_hidden_field('manufacturers_id', $HTTP_GET_VARS['manufacturers_id']);

          $options = array(array('id' => '', 'text' => TEXT_ALL_CATEGORIES));

        } else {

          echo tep_draw_hidden_field('cPath', $cPath);

          $options = array(array('id' => '', 'text' => TEXT_ALL_MANUFACTURERS));

        }

        echo tep_draw_hidden_field('sort', $HTTP_GET_VARS['sort']);

        while ($filterlist = tep_db_fetch_array($filterlist_query)) {

          $options[] = array('id' => $filterlist['id'], 'text' => $filterlist['name']);

        }

        echo tep_draw_pull_down_menu('filter_id', $options, (isset($HTTP_GET_VARS['filter_id']) ? $HTTP_GET_VARS['filter_id'] : ''), 'onchange="this.form.submit()"');

        echo tep_hide_session_id() . '</p></form></div>' . "\n";

      }

    }          
    include(DIR_WS_MODULES . FILENAME_PRODUCT_LISTING);
	
	    /*** Begin Header Tags SEO ***/ 
    if (tep_not_null($htc['htc_description'])) { 
        echo '<div class="clear"></div><center><p style="text-decoration:none;color:#ccc;margin-top:15px;">'. $htc['htc_description'] . '</p></center>';
    }
    /*** End Header Tags SEO ***/ 
?>
</div>
<?php

  } else {
      if (UNISHOP_SLIDER == 'layerslider') {
          include(DC_BLOCKS . 'top_slider.php');
      } else {
          include(DC_BLOCKS . 'top_slider_simple.php');
          include(DC_BLOCKS . 'top_slider_simple_'.UNISHOP_THEME.'.php');
      }
?>



  <?php include(DC_BLOCKS . 'slider_categories.php'); ?>
  <?php //include(DC_BLOCKS . 'slider_newproducts.php'); ?>

<?php

  }


  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');

?>

