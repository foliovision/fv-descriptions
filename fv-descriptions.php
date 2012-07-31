<?php
/*
Plugin Name: Foliopress Descriptions
Plugin URI: http://foliovision.com/seo-tools/wordpress/plugins/fv-descriptions/
Description: Mass edit descriptions for every post, page or category page. Supports post excerpt, Thesis and All In One SEO meta description fields.
Author: Foliovision
Version: 1.3.1
Author URI: http://foliovision.com

Copyright (c) 2009 Foliovision (http://foliovision.com)

Changelog:

20/10/10 -  Bug fix for categories
29/10/09 -  Bug fixes
31/03/09 -  Fixed to work with WP 2.7
*/

function fv_description_get_categories()
{
	global $wpdb;

	$categories = array();
	$results = $wpdb->get_results("SELECT * FROM ".$wpdb->categories." ORDER BY cat_name");
	return $results;
}

// this is called on plugin activation.
//add_action('admin_head','fv_description_admin_head');

function fv_description_admin_head() {

}

function fv_description_options_page()
{
	if (function_exists('add_options_page'))
	{
		/// ##Change		pBaran		18/01/2008		Foliovision
		/// Change of user level that can manage fvDescriptions
		//add_management_page('FV Descriptions', 'FV Descriptions', 10, 'manage_fv_descriptions', 'manage_fv_descriptions');
		/// use of numbers as user levels is deprecated, use some capability instead, for full list of capabilities look on Wordpress page
		add_management_page('FV Descriptions', 'FV Descriptions', 'edit_pages', 'manage_fv_descriptions', 'manage_fv_descriptions');
	}
}

add_action('admin_menu', 'fv_description_options_page');

function manage_fv_descriptions()
{
	global $wpdb;
	// for test
	//set_magic_quotes_runtime(1);
	$search_value = '';
	$search_query_string = '';
	
	///    Addition 2009/07/03
	if(isset($_POST['selectfield'])) {
        update_option('fv_descriptions_field',$_POST['selectfield']);
    }
    $fieldname = get_option('fv_descriptions_field');
    if($fieldname == '')
        $fieldname = 'excerpt';    //  default field to show
    //echo 'fv_descriptions_field: '.get_option('fv_descriptions_field').'<br />';
    //var_dump($_POST);
    /// End of addition
	
	//echo 'Reading & saving: '.$fieldname.'<br />';

	if (isset($_POST['action']) and ($_POST['action'] == 'pages'))
	{
		foreach ($_POST as $name => $value)
		{
			if(preg_match('/^tagdescription_(\d+)$/',$name,$matches))
			{
				$value = stripslashes($value);
				
				if(stripos($fieldname, 'excerpt')===FALSE) {
                    delete_post_meta($matches[1], $fieldname);
                    add_post_meta($matches[1], $fieldname, $value);
				} else {
				    $meta_value = wp_update_post(array('ID'=>$matches[1],'post_excerpt'=>$value));
                }

			}
		}

		echo '<div class="updated"><p>The custom page description have been updated.</p></div>';
	}
	elseif (isset($_POST['action']) and ($_POST['action'] == 'posts'))
	{
		foreach ($_POST as $name => $value)
		{
			if(preg_match('/^tagdescription_(\d+)$/',$name,$matches))
			{
				$value = stripslashes($value);
				
				if(stripos($fieldname, 'excerpt')===FALSE) {
                    //echo 'ID: '.$matches[1].' Desc.: '.$value.'<br />';
                    delete_post_meta($matches[1], $fieldname);
                    add_post_meta($matches[1], $fieldname, $value);
				} else {
				    $meta_value = wp_update_post(array('ID'=>$matches[1],'post_excerpt'=>$value));
                }
			}
		}

		echo '<div class="updated"><p>The custom post description have been updated.</p></div>';
	}
	elseif (isset($_POST['action']) and ($_POST['action'] == 'categories'))
	{
		foreach ($_POST as $name => $value)
		{
			if(preg_match('/^description_(\d+)$/',$name,$matches))
			{
				$description = stripslashes($_POST['description_'.$matches[1]]);
				$description = $wpdb->escape($description);
				  //echo '<hr />'.$matches[1].'. '.$description.'<br />';
				//$table_name = $wpdb->prefix . "categories";
				$category = get_category($matches[1], ARRAY_A);
				//$category = add_magic_quotes($category);
				  //var_dump($category);
				  //echo '<hr />';
				$category['description'] = $description;
				//var_dump( $category );
				  //var_dump($category);
				wp_insert_category($category);
				
				//$temp = $wpdb->get_row("SELECT cat_ID from ".$table_name." where cat_ID = ".$matches[1]);

				if ($temp->cat_ID == $matches[1])
				{
					//$wpdb->query("UPDATE ".$table_name." SET category_description = '".$description."' where cat_ID = ".$matches[1]);
				}
			}
		}

		echo '<div class="updated"><p>The custom Category description have been saved.</p></div>';
	}
	elseif (isset($_POST['search_value']))
	{
		$search_value = $_POST['search_value'];
	}

	if (! isset($_POST['search_value']))
	{
		$search_value = $_GET['search_value'];
	}

	$description_tags_type = $_GET['description_tags_type'];
	$page_no = $_GET['page_no'];
	$manage_elements_per_page = get_option("manage_elements_per_page");
	$element_count = 0;

	if(empty($manage_elements_per_page))
	{
		$manage_elements_per_page = 15;
	}

	$_SERVER['QUERY_STRING'] = preg_replace('/&description_tags_type=[^&]+/','',$_SERVER['QUERY_STRING']);
	$_SERVER['QUERY_STRING'] = preg_replace('/&page_no=[^&]+/','',$_SERVER['QUERY_STRING']);
	$_SERVER['QUERY_STRING'] = preg_replace('/&search_value=[^&]*/','',$_SERVER['QUERY_STRING']);
	$search_query_string = '&search_value='.$search_value;
	//echo get_option('manage_elements_per_page');

	if(! $page_no)
	{
		$page_no = 0;
	}
	?>

    <div class="wrap">
        <div id="icon-tools" class="icon32"><br /></div>
        
        <h2>FV Descriptions</h2>
        	        
	<ul class="subsubsub">
	<?php $url = preg_replace('/&description_tags_type=.*?$/','',$_SERVER['REQUEST_URI']) ?>
        <li><a href="<?php echo $url.'&description_tags_type=pages'; ?>" <?php echo fv_is_current($_REQUEST['description_tags_type'],'pages'); if ($_REQUEST['description_tags_type']=='') echo 'class=current'; ?>>Pages</a></li>
        <li><a href="<?php echo $url.'&description_tags_type=posts'; ?>" <?php echo fv_is_current($_REQUEST['description_tags_type'],'posts'); ?>>Posts</a></li>
        <li><a href="<?php echo $url.'&description_tags_type=categories'; ?>" <?php echo fv_is_current($_REQUEST['description_tags_type'],'categories'); ?>>Categories</a></li>
    </ul>
    
    <div style="text-align: right;">
		<form action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post">
			<input type="text" name="search_value" value="<?php if (isset($search_value)) echo $search_value; ?>" size="17" />
			<input type="submit" value="Search" class="button" />
		</form>
	</div>

    <div class="tablenav">
        <div class="alignleft actions">
            <form name="selectform" action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post">
                Select field to display: <select name="selectfield">
                    <option value="excerpt"<?php if($fieldname=="excerpt") echo ' selected'; ?>>post_excerpt</option>
                    <option value="thesis_description"<?php if($fieldname=="thesis_description") echo ' selected'; ?>>thesis_description</option>
                    <option value="description"<?php if($fieldname=="description") echo ' selected'; ?>>All In One SEO</option>
                    <option value="_aioseop_description"<?php if($fieldname=="_aioseop_description") echo ' selected'; ?>>All In One SEO 1.6.2</option>
                </select>
                <input type="submit" value="Apply" name="doaction" id="doaction" class="button-secondary action" />
            </form>
        </div>
    </div>  
	

	<?php
	if (isset($_POST['info_update'])) {
		update_option("manage_elements_per_page", $_POST['manage_elements_per_page']);
	}
		$manage_elements_per_page = get_option("manage_elements_per_page");
	?>	
	
	<fieldset class="options">
        <?php
        ///  Addition 25/03/09 mVicenik Foliovision
        //   $manage_elements_per_page was preset to 0 in WP 2.7
        if($manage_elements_per_page==0) $manage_elements_per_page = 10;
        ///  End of addition

        if((empty($description_tags_type)) or ($description_tags_type == 'pages'))
        {
               if (!empty($search_value)) {
                  $sql = ' AND (post_title LIKE "%'.$search_value.'%")';
                }
                
                $pages = $wpdb->get_results('SELECT * FROM '.$wpdb->posts.' WHERE post_type = "page" '.$sql.'ORDER BY post_date DESC LIMIT '.$page_no*$manage_elements_per_page.','.$manage_elements_per_page);

                $element_count = $wpdb->get_var('SELECT COUNT(ID) FROM '.$wpdb->posts.' WHERE post_type = "page" '.$sql.' ORDER BY post_date DESC');
                                       
                if ($pages)
                {
        ?>
                        <form name="pages-form" action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post">
			<div class="left"><input type="submit" value="Press before leaving this page to save your changes" /> </div><div class="clearer"></div>
                        <input type="hidden" name="action" value="pages" />
                        <table class="widefat">
                        <thead>
                        <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Title</th>
                        <th scope="col">Description</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php

                        if ((($element_count > $manage_elements_per_page) and (($page_no != 'all') or empty($page_no))) or (! empty($search_value)))
                        {
                        	manage_fv_descriptions_recursive('pages',0,0,$pages,false,$fieldname);
                        }
                        else
                        {
                        	manage_fv_descriptions_recursive('pages',0,0,$pages,true,$fieldname);
                        }

                        echo '</tbody></table><div class="left"><input type="submit" value="Press before leaving this page to save your changes" /></div></form>';
                }
                else
                {
                	echo '<p><b>No pages found!</b></p>';
                }
        }
        elseif ($description_tags_type == 'posts')
        {

                if (!empty($search_value)) {
                  $sql = ' AND (post_title LIKE "%'.$search_value.'%")';
                }
                
                $posts = $wpdb->get_results('SELECT * FROM '.$wpdb->posts.' WHERE post_type = "post" '.$sql.'ORDER BY post_date DESC LIMIT '.$page_no*$manage_elements_per_page.','.$manage_elements_per_page);         
               
               //echo count($posts).' posts queried '; 
               //echo 'Page/PerPage '.$page_no.'/'.$manage_elements_per_page.' ';
               //echo 'Limit: '.$page_no*$manage_elements_per_page.','.($page_no+1)*$manage_elements_per_page;
                
                $element_count = $wpdb->get_var('SELECT COUNT(ID) FROM '.$wpdb->posts.' WHERE post_type = "post" '.$sql.' ORDER BY post_date DESC');

                if ($posts)
                {
                        ?>
						<form name="posts-form" action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post">
						<div class="left"><input type="submit" value="Press before leaving this page to save your changes" /> </div><div class="clearer"></div>
                        <input type="hidden" name="action" value="posts" />
                        <table class="widefat">
                        <thead>
                        <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Title</th>
                        <th scope="col">Description</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        manage_fv_descriptions_recursive('posts',0,0,$posts,true,$fieldname);

                        echo '</table><div class="left"><input type="submit" value="Press before leaving this page to save your changes" /> </div></form>';
                }
                else
                {
                	echo '<p><b>No posts found!</b></p>';
                }
        }
        elseif ($description_tags_type == 'categories')
        {

                /*$table_name = $wpdb->prefix . "categories";
		
                $categories = fv_description_get_categories();*/
                $categories = get_categories();//'post','','ID','asc',false,false,true,'','','');

                $category_descriptions;

                foreach ($categories as $category){
                  $category_descriptions[$category->cat_ID] = $category->category_description;
                }

                if  (!empty($search_value))
                {
                  /// Modification   25/03/09 mVicenik Foliovision
                	/*$sql = 'SELECT * from '.$table_name;

                	if(!empty($search_value))
                	{
                		$sql .= ' WHERE category_description LIKE "%'.$wpdb->escape($search_value).'%" ';
                		//OR wp_posts LIKE "%'.$wpdb->escape($search_value).'%"';
                	}

                	$category_descriptions = $wpdb->get_results($sql);

                	$category_descriptions_new;

                	foreach ($category_descriptions as $category_description)
                	{
                		$category_descriptions_new[$category_description->cat_ID] = $category_description->category_description;
                	}

                	//lets rebuild the categories killing all items that don't have this category ID
                	if(count($category_descriptions_new)>0) {
                		foreach($categories as $key => $value ){

                			if(!isset($category_descriptions_new[$value->cat_ID])) {
                				unset($categories[$key]);
                			}
                		}
                	} else {
                		$categories=array();
                	}
                	*/             	
                	$category_descriptions_new;
                	foreach ($category_descriptions as $key => $value)
                	{
                		if(stripos($value,$search_value)!==FALSE) {
                		   //$categories_new[$key] = $category[$key];
                		   $category_descriptions_new[$key] = $category_descriptions[$key];
                		}
                	}
                  
                	$category_descriptions = $category_descriptions_new;
                	
                	foreach($categories AS $key => $value) {
                	    if(!isset($category_descriptions[$value->cat_ID]))
                	       unset($categories[$key]);
                   }
                   /// End of modification
                } else {
                	//defult filling of the category descriptions field.
                	/// Modification   25/03/09 mVicenik Foliovision
                	/*$sql = 'SELECT cat_ID, category_description from '.$table_name;
                	$category_descriptions = $wpdb->get_results($sql);

                	$category_descriptions_new;
					
                	if ($category_descriptions)
                	{
                		foreach ($category_descriptions as $category_description)
                		{
                			$category_descriptions_new[$category_description->cat_ID] = $category_description->category_description;
                		}
                		
                		$category_descriptions = $category_descriptions_new;
                	}*/
                	/// End of modification

                }

                $element_count = count($categories);

                if (($element_count > $manage_elements_per_page) and (($page_no != 'all') or empty($page_no)))
                {
                	if($page_no > 0)
                	{
                		$categories = array_splice($categories, ($page_no * $manage_elements_per_page));
                	}

                	$categories = array_slice($categories, 0, $manage_elements_per_page);
                }

                if($categories) {
                ?>
				<form name="categories-form" action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post">
				<div class="left"><input type="submit" value="Press before leaving this page to save your changes" /> </div><div class="clearer"></div>
                <input type="hidden" name="action" value="categories" />
                <table class="widefat">
                <thead>
                <tr>
                <th scope="col">Category ID</th>
                <th scope="col">Category</th>
                <th scope="col">Description</th>
                </tr>
                </thead>
                <tbody>
                <?php

                foreach ($categories as $category)
                {
						$category_value = $category_descriptions[$category->cat_ID];
						
						if (get_magic_quotes_runtime())
						{
							$category_value = stripslashes($category_value);
						}
                        ?>
                        <tr>
                        <td><a href="<?php echo get_category_link($category->cat_ID) ?>"><?php echo $category->cat_ID ?></a></td>
                        <td><?php echo $category->cat_name ?></td>
                        <td><input type="text" name="description_<?php echo $category->cat_ID ?>" value="<?php echo $category_value; ?>" size="70" /></td>
                        <?php
                }

                echo '</table><div class="left"><input type="submit" value="Press before leaving this page to save your changes" /> </div></form>';

                } else { //End of check for categories
                	print "<b>No Categories found!</b>";
                }
        }
        else
        {
        	echo '<p>unknown description tags type!</p>';
        }

        ?>

        </fieldset>

	

        <?php
        if($element_count > $manage_elements_per_page)
        {
        	if(($page_no == 'all') and (! empty($page_no)))
        	{
        		echo 'View All&nbsp;&nbsp;';
        	}
        	else
        	{
        		echo '<a href="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&page_no=all&description_tags_type='.$description_tags_type.$search_query_string.'">View All</a>&nbsp;&nbsp;';
        	}
        }

        if($element_count > $manage_elements_per_page)
        {
        	/// Add		pBaran		18/01/2008		Foliovision
        	// Division by zero was ocurying on 2 lines below. fixed this with the line below, but real author should check this and fix it.
        	if( 0 == $manage_elements_per_page ) $manage_elements_per_page = 1;
        	for ($p = 0; $p < (int) ceil($element_count / $manage_elements_per_page); $p++)
        	{
        		if ($page_no == $p)
        		{
        			echo ($p + 1).'&nbsp;';
        		}
        		else
        		{
        			echo '<a href="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&page_no='.$p.'&description_tags_type='.$description_tags_type.$search_query_string.'">'.($p + 1).'</a> ';
        		}
        	}
        }
        ?>
		<div class="right">
			<form name="stto_main" method="post">
				Post per page:<input name="manage_elements_per_page" value="<?php echo $manage_elements_per_page; ?>" size="5" class="code" />
				<input type="hidden" name="action" value="update" />
				<input type="hidden" name="info_update" value="Update Options" />
				<!--<input type="submit" name="submit-option" value="Show" />-->
			</form>
		</div><div class="clearer"></div>
		<div style="text-align: right;">
            <a href="http://foliovision.com"><img alt="visit foliovision" src="http://foliovision.com/shared/fv-logo.png" /></a>
		</div>
        </div>
        <?php
}

function manage_fv_descriptions_recursive($type, $parent = 0, $level = 0, $elements = 0, $hierarchical = true, $fieldname)
{
	if (! $elements)
	{
		return;
	}

	foreach($elements as $element)
	{
		if (($element->post_parent != $parent) and $hierarchical)
		{
			continue;
		}

		$element_custom = get_post($element->ID); 

		$pad = str_repeat( '&#8212; ', $level );
		$element_value = $element_custom->post_excerpt;
		if (get_magic_quotes_runtime())
		{
			$element_value = stripslashes($element_value);
		}
                ?>
                <tr>
                <td><a href="<?php echo get_permalink($element->ID) ?>"><?php echo $element->ID ?></a></td>
                <td><?php echo $pad.$element->post_title ?></td>
                <?php   ///   Modification 23/06/2009  Foliovision?>
                <?php if($fieldname=='excerpt') : ?>
                <td><input type="text" title="<?php echo htmlspecialchars( $element->post_description ); ?>" name="tagdescription_<?php echo $element->ID ?>" id="tagdescription_<?php echo $element->ID ?>" value="<?php echo htmlspecialchars ($element_value); ?>" size="80" /></td>
                <?php else : ?>
                <td><input type="text" title="<?php echo htmlspecialchars( trim(stripcslashes(get_post_meta($element->ID, $fieldname, true))) ); ?>" name="tagdescription_<?php echo $element->ID ?>" id="tagdescription_<?php echo $element->ID ?>" value="<?php echo htmlspecialchars( trim(stripcslashes(get_post_meta($element->ID, $fieldname, true))) ); ?>" size="80" /></td>
                <?php endif; ?>
                <?php   ///   End of modifications ?>
                
                <!-- <td><?php //echo $element->post_type ?></td> -->
                <?php

                if ($hierarchical)
                {
                	manage_fv_descriptions_recursive($type, $element->ID,$level + 1, $elements, $hierarchical, $fieldname);
                }
	}
}

//returns class=current if the strings exist and match else nothing.
//Used down on the top nav to select which page is selected.
function fv_is_current($aRequestVar,$aType) {
	if(!isset($aRequestVar) || empty($aRequestVar)) { return; }
	//do the match
	if($aRequestVar == $aType) { return 'class=current'; }
}

?>
