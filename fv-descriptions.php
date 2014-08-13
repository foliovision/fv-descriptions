<?php    
/*
Plugin Name: FV Descriptions
Plugin URI: http://foliovision.com/seo-tools/wordpress/plugins/fv-descriptions/
Description: Mass edit descriptions for every post, page or category page. Supports post excerpt, Thesis and All In One SEO meta description fields.
Author: Foliovision
Version: 1.3.3
Author URI: http://foliovision.com

Copyright (c) 2009 Foliovision (http://foliovision.com)

Changelog:

08/08/14 -  Fixed select of items to be displayed in Description column
18/07/14 -  Fixed paging, saving options, design
17/07/14 -  Editing of posts and pages with statuses Published, Future, Pending or Private
11/12/12 -  Fixed search
04/12/12 -  Items per page in screen options
22/11/12 -  Added mass editing of titles and keywords
20/10/10 -  Bug fix for categories
29/10/09 -  Bug fixes
31/03/09 -  Fixed to work with WP 2.7
*/


class FvDescriptionAdmin {

   private static $idManagementPage = null;

   public static function AddManagement(){
      self::$idManagementPage = add_management_page(
         'FV Descriptions',
         'FV Descriptions',
         'edit_pages',
         'fv_descriptions',
         'manage_fv_descriptions'
      );
      add_option( 'fv_items_per_page', '10' );
   }
   
   public function ScreenOptions( $strHTML, $objScreen ){
      if( $objScreen->id == self::$idManagementPage ){
         $strHTML .= 
            '<form name="my_option_form" method="post">
               <br />
               &nbsp;Items per page
               <input type="text" class="screen-per-page" value="'.get_option( 'fv_items_per_page' ).'" name="fv-items-per-page" />
               <input type="submit" class="button" value="Apply">
               <br />
               <br />
	       </form> ';
	     	          
      }

      return $strHTML;
   }

}

if( is_admin() ){
   add_action( 'admin_menu', array( 'FvDescriptionAdmin', 'AddManagement' ) );
   add_filter( 'screen_settings', array( 'FvDescriptionAdmin', 'ScreenOptions' ), 10, 2 );
}

function save_my_option(){
   if( isset( $_POST['fv-items-per-page'] ) ){
      update_option( 'fv_items_per_page', $_POST['fv-items-per-page'] );
   }
}
 
add_action('admin_init', 'save_my_option');


function fv_get_field_type() 
{
  $type = $_REQUEST["description_field_type"];
  
  if(!isset($type)) {
    $type = 'description';
  }
  
  return $type;
}

function fv_get_tag_type() 
{
  $type = $_REQUEST["description_tags_type"];
  
  if(!isset($type)) {
    $type = 'pages';
  }
  
  return $type;
}


function fv_detect_plugin()
{
  $plugins = get_option('active_plugins');
  foreach ( $plugins AS $plugin ) {
	  if( stripos($plugin,'all-in-one-seo-pack') !== FALSE ) {
      return '_aioseop_description';
    }
  }
  return 'excerpt';
}

function manage_fv_descriptions()
{
	global $wpdb;

	$search_value = '';
	$search_query_string = '';
	
	 if(isset($_GET['selectfield'])) {
	    update_option('fv_descriptions_field',$_GET['selectfield']);
	 }elseif(get_option('fv_descriptions_field') === FALSE ){	    	     
	    update_option('fv_descriptions_field',fv_detect_plugin()); 
	 }
	 
	 $fieldname = get_option('fv_descriptions_field');

if(isset($_POST['action'])) {
   
   if(wp_verify_nonce($_POST['hash'],'fv_'.fv_get_field_type().fv_get_tag_type())){
	
	if (isset($_POST['action']) and ($_POST['action'] == 'pages')){
	        
		foreach ($_POST as $name => $value){		  
			$value = stripslashes($value);
			
			if(fv_get_field_type() == 'description' or fv_get_field_type() == 'bothatonce'){
			   
			      if(preg_match('/^tagdescription_(\d+)$/',$name,$matches)){
				 
				 if(stripos($fieldname, 'excerpt')=== FALSE){				    
				     update_post_meta($matches[1], $fieldname, $value);
				 }else {
				    $meta_value = wp_update_post(array('ID'=>$matches[1],'post_excerpt'=>$value)); 
				 }
			      }
			}
      
			if(fv_get_field_type() == 'title' or fv_get_field_type() == 'bothatonce'){
			   
			      if(preg_match('/^tagtitle_(\d+)$/',$name,$matches)){
				    $meta_value = wp_update_post(array('ID'=>$matches[1],'post_title'=>$value));
			      }
			}
		}
		
	 echo '<div class="updated"><p>The custom page description / title have been updated.</p></div>';
	 
	}elseif (isset($_POST['action']) and ($_POST['action'] == 'posts')){
		
		foreach ($_POST as $name => $value){
			$value = stripslashes($value);
			
			if(fv_get_field_type() == 'description' or fv_get_field_type() == 'all3atonce'){	 
			   if(preg_match('/^tagdescription_(\d+)$/',$name,$matches)){	
			      if(stripos($fieldname, 'excerpt')===FALSE){
				 delete_post_meta($matches[1], $fieldname);
				 add_post_meta($matches[1], $fieldname, $value);
			      }else {
				 $meta_value = wp_update_post(array('ID'=>$matches[1],'post_excerpt'=>$value));
			      }
			   }
			}
			
			if(fv_get_field_type() == 'title' or fv_get_field_type() == 'all3atonce'){
			   if(preg_match('/^tagtitle_(\d+)$/',$name,$matches)){
			      $meta_value = wp_update_post(array('ID'=>$matches[1],'post_title'=>$value));
			   }
			}
			
			if(fv_get_field_type() == 'keywords' or fv_get_field_type() == 'all3atonce'){
			   if(preg_match('/^tagkeywords_(\d+)$/',$name,$matches)){ 
			      wp_set_post_tags($matches[1], $value ,true);                   
			   }  
			}       
		}
		echo '<div class="updated"><p>The custom post description / title / keywords have been updated.</p></div>';
	
	}elseif (isset($_POST['action']) and ($_POST['action'] == 'categories')){
	 
		foreach ($_POST as $name => $value){
			if(fv_get_field_type() == 'description' or fv_get_field_type() == 'bothatonce'){
			   if(preg_match('/^description_(\d+)$/',$name,$matches)){
				 $description = stripslashes($_POST['description_'.$matches[1]]);
				 $description = $wpdb->escape($description);
				 $category = get_category($matches[1], ARRAY_A);
				 $category['description'] = $description;
			   }
			}
			
			if(fv_get_field_type() == 'title' or fv_get_field_type() == 'bothatonce'){
			   if(preg_match('/^title_(\d+)$/',$name,$matches)){ 
				 $description = stripslashes($_POST['title_'.$matches[1]]);
				 $description = $wpdb->escape($description);
				 $category = get_category($matches[1], ARRAY_A);
				 $category['name'] = $description;
			   }
			}
			wp_insert_category($category);
		}
		echo '<div class="updated"><p>The custom Category description / title have been saved.</p></div>';
		
	}elseif (isset($_POST['search_value'])){
		$search_value = esc_sql($_POST['search_value']);
	}
   } else {
    echo '<div class="error"><p>Nonce verification failed.</p></div>';
   }
}
	
  $search_value = (isset($_POST['search_value']))
      ? $_POST['search_value']
      : $_GET['search_value'];
 
        global $description_tags_type;
	$description_tags_type = $_GET['description_tags_type'];
	
        $page_no = $_GET['page_no'];
	
	    /*Here($aUnwanted_post_status) you can set up, which post_status you won't have in your search.**/
	    $aUnwanted_post_status=array('draft','trash','auto-draft','inherit');
	    $sSql_unwanted_post_status='';
	 
	       for($i=0;$i<count($aUnwanted_post_status);$i++){
		  if($i!=count($aUnwanted_post_status)-1)
		       $sSql_unwanted_post_status.='post_status !="'.$aUnwanted_post_status[$i].'" AND ';
	         else
		      $sSql_unwanted_post_status.='post_status !="'.$aUnwanted_post_status[$i].'" ';
		   }	 
	    
	 
	
	$element_count = 0;

	$_SERVER['QUERY_STRING'] = preg_replace('/&description_tags_type=[^&]+/','',$_SERVER['QUERY_STRING']);
	$_SERVER['QUERY_STRING'] = preg_replace('/&page_no=[^&]+/','',$_SERVER['QUERY_STRING']);
	$_SERVER['REQUEST_URI'] = preg_replace('/&page_no=[^&]+/','',$_SERVER['REQUEST_URI']);
	$_SERVER['QUERY_STRING'] = preg_replace('/&search_value=[^&]*/','',$_SERVER['QUERY_STRING']);
	$search_query_string = '&search_value='.$search_value;

	if(!isset($page_no))
	{
		$page_no = 1;
	}
	?>

    <div class="wrap">
    
        <div style="position: absolute; top: 30px; right: 10px;">
            <a href="http://foliovision.com/seo-tools/wordpress/plugins/foliopress-descriptions" target="_blank" title="Documentation"><img alt="visit foliovision" src="http://foliovision.com/shared/fv-logo.png" /></a>
		    </div>
 
        <div>
            <div id="icon-tools" class="icon32"><br /></div>       
            <h2>FV Descriptions</h2>
        </div>
  

  	        
	  <ul class="subsubsub">
	      <li>Display:</li>
	<?php $url = preg_replace('/&description_tags_type=.*?$/','',$_SERVER['REQUEST_URI']); ?>
        <li><a href="<?php echo $url.'&description_tags_type=pages&page_no=1'; ?>" <?php echo fv_is_current($_REQUEST['description_tags_type'],'pages'); if ($_REQUEST['description_tags_type']=='') echo 'class=current'; ?>>Pages</a></a>(<?php  $pages = wp_count_posts('page'); echo $pages->publish+ $pages->pending+ $pages->future+ $pages->private; ?>) |</li>
        <li><a href="<?php echo $url.'&description_tags_type=posts&page_no=1'; ?>" <?php echo fv_is_current($_REQUEST['description_tags_type'],'posts'); ?>>Posts</a>(<?php $postss = wp_count_posts('post');  echo $postss->publish+$postss->pending+$postss->future+$postss->private; ?>) |</li> 
        <li><a href="<?php echo $url.'&description_tags_type=categories&page_no=1'; ?>" <?php echo fv_is_current($_REQUEST['description_tags_type'],'categories'); ?>>Categories</a>(<?php $categories = get_categories(); $element_count = count($categories); echo ($element_count); ?>)</li>
    </ul>

    <br /><br />
    
    <ul class="subsubsub" style="position: absolute; left: 0px;">
    <li>Change:</li>
    <?php $url = preg_replace('/&description_field_type=\w+/','',$_SERVER['REQUEST_URI']) ?>
      <li><a href="<?php echo $url.'&description_field_type=description&page_no='.$page_no; ?>" <?php echo fv_is_current($_REQUEST['description_field_type'],'description'); if ($_REQUEST['description_field_type']=='') echo 'class=current'; ?>>Description</a> | </li>
      <li><a href="<?php echo $url.'&description_field_type=title&page_no='.$page_no; ?>" <?php echo fv_is_current($_REQUEST['description_field_type'],'title'); ?>>Title</a> | </li>
      <?php if ($description_tags_type != 'posts') { ?>
      <li><a href="<?php echo $url.'&description_field_type=bothatonce&page_no='.$page_no; ?>" <?php echo fv_is_current($_REQUEST['description_field_type'],'bothatonce'); ?>>Both at once</a></li> 
      <?php } ?>
      <?php if ($description_tags_type == 'posts') { ?>
      <li><a href="<?php echo $url.'&description_field_type=keywords&page_no='.$page_no; ?>" <?php echo fv_is_current($_REQUEST['description_field_type'],'keywords'); ?>>Keywords</a> | </li>
      <li><a href="<?php echo $url.'&description_field_type=all3atonce&page_no='.$page_no;?>" <?php echo fv_is_current($_REQUEST['description_field_type'],'all3atonce'); ?>>All 3 at once</a></li> 
      <?php } ?>
    </ul>
    
    <br />

    
    <div style="text-align: right;">
		<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
			<input type="text" name="search_value" value="<?php if (isset($search_value)) echo $search_value; ?>" size="17" />
			<input type="submit" value="Search" class="button" />
		</form>                       
	</div>
    <?php if($_GET['description_tags_type'] !== 'categories') { ?>
    <div class="tablenav">
        <div class="alignleft actions">
            Select field to display in Description column:
	    <form name="selectform" method="get">
	       <input type="hidden" name="page" value="fv_descriptions">
	       <input type="hidden" name="description_tags_type" value="<?php if($description_tags_type){echo $description_tags_type;}else{echo "pages" ;} ?>">
	       <input type="hidden" name="page_no" value="<?php echo $page_no;?>">
	       <input type="hidden" name="description_field_type" value="<?php echo fv_get_field_type();?>">
		<select name="selectfield">
                    <option value="excerpt"<?php if($fieldname=="excerpt") echo ' selected';  ?>>Excerpt</option>
                    <option value="thesis_description"<?php if($fieldname=="thesis_description") echo ' selected'; ?>>thesis_description</option>
                    <option value="_aioseop_description"<?php if($fieldname=="_aioseop_description") echo ' selected'; ?>>All In One SEO Meta Description</option>
                </select>
                <input type="submit" value="Apply" class="button-secondary action" />
            </form>
        </div>
    </div><br>
    <?php } ?>
    
	<fieldset class="options">
        <?php
         
	 
	 /*Here($aUnwanted_post_status) you can set up, which post_status you won't have in your search.**/
	 $aUnwanted_post_status=array('draft','trash','auto-draft','inherit');
	 $sSql_unwanted_post_status='';
	 
	 for($i=0;$i<count($aUnwanted_post_status);$i++){
	    if($i!=count($aUnwanted_post_status)-1)
	       $sSql_unwanted_post_status.='post_status !="'.$aUnwanted_post_status[$i].'" AND ';
	    else
	       $sSql_unwanted_post_status.='post_status !="'.$aUnwanted_post_status[$i].'" ';
	 }	 
	 
	 
        if((empty($description_tags_type)) or ($description_tags_type == 'pages')){
	 
                if (!empty($search_value)) {
                  $sql = ' AND (post_title LIKE "%'.$search_value.'%")';
                }
                
                $pages = $wpdb->get_results('SELECT * FROM '.$wpdb->posts.' WHERE post_type = "page" AND '.$sSql_unwanted_post_status .$sql.'ORDER BY post_date DESC LIMIT '.($page_no - 1)*get_option( 'fv_items_per_page' ).','.get_option( 'fv_items_per_page' ));

                $element_count = $wpdb->get_var('SELECT COUNT(ID) FROM '.$wpdb->posts.' WHERE post_type = "page" AND '.$sSql_unwanted_post_status.$sql.' ORDER BY post_date DESC');       
                
	        if(isset($_POST['fv-items-per-page'])){?>
		  <form name="hidden_form">
		     <input type="hidden" name="page" value="fv_descriptions">
		     <input type="hidden" name="description_tags_type" value="<?php if($description_tags_type){echo $description_tags_type;}else{echo "pages" ;} ?>">
		     <input type="hidden" name="description_field_type" value="<?php echo fv_get_field_type();?>">
		     <input type="hidden" name="page_no" value="<?php echo get_last_page_no($element_count); ?>">
		  </form>
		  <script>
		  document.hidden_form.submit();
		  </script>		  
		<?php }
                ?>                           
                <div class="tablenav top">
                  <div class="tablenav-pages" style="line-height: 10px;">
		     <span class="pagination-links">
			<span class="displaying-num">
                        <center>Displaying <?php echo ($page_no - 1) * get_option( 'fv_items_per_page' ) + 1; ?> -
                        <?php 
                        if ( ( $page_no * get_option( 'fv_items_per_page' ) ) > $element_count )
                        echo $element_count;
                        else echo $page_no * get_option( 'fv_items_per_page' ); 
                        ?> of <?php
			echo $element_count.' ';
			if($element_count>1){
			   echo "items";
			}else{
			   echo "item";
			}
			?>			
			</center> 
			</span>
		     </span>
		     <span class="pagination-links">		   
			<form method="GET">
                      <?php 
                      
		      			
			$max_page=ceil($element_count/get_option('fv_items_per_page'));
			if($page_no>$max_page) {
			   $page_no=$max_page;
			}
			
			
                      
		      
		      if ( ( $page_no * get_option( 'fv_items_per_page' ) ) < $element_count){
			if($page_no<1){
			  $page_no=1;
			  }
		      }
			$prev_page=$page_no-1;
                        $next_page=$page_no+1;
			
		      if ($page_no > 1)			
		      echo '<a class="prev-page" href="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&page_no='.$prev_page.'&description_tags_type='.$description_tags_type.$search_query_string.'">&laquo;</a>'; ?>
		      <?php
			if(get_option( 'fv_items_per_page' )>= $element_count){
			echo '</div></div>';
			}else{
			?>
			      <input type="hidden" name="page" value="fv_descriptions">
			      <input type="hidden" name="description_tags_type" value="<?php if($description_tags_type){echo $description_tags_type;}else{echo "pages" ;} ?>">
			      <input type="hidden" name="description_field_type" value="<?php echo fv_get_field_type();?>">
			      <input type="hidden" name="page_no" value="<?php echo $page_no;?>">
			      Go to page:
			      <input type="number" style="width: <?php echo get_style_width_listing_input($max_page).'px' ; ?>;" name="page_no" value="<?php echo $page_no;?>"  max="<?php echo $max_page; ?>" min="1">			      
			      <input type="submit" value="GO">
			      
		      
                      <?php if ( ( $page_no * get_option( 'fv_items_per_page' ) ) < $element_count)
		      echo '<a class="next-page" href="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&page_no='.$next_page.'&description_tags_type='.$description_tags_type.$search_query_string.'">&raquo;</a>';
		      ?>   
			</form>			
		     </span> 
                  </div>
                </div>
		
                <?php                                
		     }
		     
		if ($pages)
                {
                ?>
                <form name="pages-form" action="<?php echo $_SERVER['REQUEST_URI'].'&page_no='.$page_no; ?>" method="post">
			            <div class="left"><input type="submit" value="Press before leaving this page to save your changes" /> </div><div class="clearer"></div>
                  <input type="hidden" name="action" value="pages" /> 
                  <table class="widefat">  
                    <thead> 
                      <tr>   
                      <th scope="col" width="70">ID</th>    
                      <th scope="col" width="250">Title</th>
                      <th scope="col">Description</th> 
                      </tr>    
                    </thead>   
                    <tbody> 
                        <?php
                        if ((($element_count > get_option( 'fv_items_per_page' )) and (($page_no != 'all') or empty($page_no))) or (! empty($search_value)))
                        {
                        	manage_fv_descriptions_recursive('pages',0,0,$pages,false,$fieldname);
                        }
                        else
                        {
                        	manage_fv_descriptions_recursive('pages',0,0,$pages,true,$fieldname);
                        }
                      wp_nonce_field('fv_'.fv_get_field_type().fv_get_tag_type(),'hash');  
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
		
	        $posts = $wpdb->get_results('SELECT * FROM '.$wpdb->posts.' WHERE post_type = "post" AND '.$sSql_unwanted_post_status.$sql.'ORDER BY post_date DESC LIMIT '.( $page_no - 1 ) * get_option( 'fv_items_per_page' ).','.get_option( 'fv_items_per_page' ));

                $element_count = $wpdb->get_var('SELECT COUNT(ID) FROM '.$wpdb->posts.' WHERE post_type = "post" AND '.$sSql_unwanted_post_status.$sql.' ORDER BY post_date DESC');       
                 
	       if(isset($_POST['fv-items-per-page'])){?>
		  <form name="hidden_form">
		     <input type="hidden" name="page" value="fv_descriptions">
		     <input type="hidden" name="description_tags_type" value="<?php if($description_tags_type){echo $description_tags_type;}else{echo "pages" ;} ?>">
		     <input type="hidden" name="description_field_type" value="<?php echo fv_get_field_type();?>">
		     <input type="hidden" name="page_no" value="<?php echo get_last_page_no($element_count); ?>">
		  </form>
		  <script>
		  document.hidden_form.submit();
		  </script>		  
		<?php }
                ?>
                <div class="tablenav top">
                  <div class="tablenav-pages" style="line-height: 10px;">
		     <span class="pagination-links">
			<span class="displaying-num">
                        <center>Displaying <?php echo ($page_no - 1) * get_option( 'fv_items_per_page' ) + 1; ?> -
                        <?php 
                        if ( ( $page_no * get_option( 'fv_items_per_page' ) ) > $element_count )
                        echo $element_count;
                        else echo $page_no * get_option( 'fv_items_per_page' ); 
                        ?> of <?php
			echo $element_count.' ';
			if($element_count>1){
			   echo "items";
			}else{
			   echo "item";
			}?>
			</center>
			</span>
		     </span>
		     <span class="pagination-links">		   
			<form method="GET">
                      <?php 
                      
		      			
			$max_page=ceil($element_count/get_option('fv_items_per_page'));
			if($page_no>$max_page) {
			   $page_no=$max_page;
			}
			
			
                      
		      
		      if ( ( $page_no * get_option( 'fv_items_per_page' ) ) < $element_count){
			if($page_no<1){
			  $page_no=1;
			  }
		      }
			$prev_page=$page_no-1;
                        $next_page=$page_no+1;
			
		      if ($page_no > 1)			
		      echo '<a class="prev-page" href="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&page_no='.$prev_page.'&description_tags_type='.$description_tags_type.$search_query_string.'">&laquo;</a>'; ?>
		      <?php
			if(get_option( 'fv_items_per_page' )>= $element_count){
			echo '</div></div>';
			}else{
			?>
			      <input type="hidden" name="page" value="fv_descriptions">
			      <input type="hidden" name="description_tags_type" value="<?php if($description_tags_type){echo $description_tags_type;}else{echo "pages" ;} ?>">
			      <input type="hidden" name="description_field_type" value="<?php echo fv_get_field_type();?>">
			      Go to page:
			      <input type="number" style="width: <?php echo get_style_width_listing_input($max_page).'px' ; ?>;" name="page_no" value="<?php echo $page_no;?>"  max="<?php echo $max_page; ?>" min="1">			      
			      <input type="submit" value="GO">
			      
		      
                      <?php if ( ( $page_no * get_option( 'fv_items_per_page' ) ) < $element_count)
		      echo '<a class="next-page" href="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&page_no='.$next_page.'&description_tags_type='.$description_tags_type.$search_query_string.'">&raquo;</a>';
		      ?>   
			</form>			
		     </span> 
                  </div>
                </div>
		
                <?php
		     }
                if ($posts)
                {
                        ?>
						<form name="posts-form" action="<?php echo $_SERVER['REQUEST_URI'].'&page_no='.$page_no; ?>" method="post">
						 
						<div class="left"><input type="submit" value="Press before leaving this page to save your changes" /> </div><div class="clearer"></div>
                        <input type="hidden" name="action" value="posts" />
                        <table class="widefat">
                        <thead>
                        <tr>
                        <th scope="col" width="70">ID</th>
                        <th scope="col" width="250">Title</th>
                        <th scope="col" width="250">Description</th>                        
                        <th scope="col">Keywords</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        manage_fv_descriptions_recursive('posts',0,0,$posts,true,$fieldname);
                        wp_nonce_field('fv_'.fv_get_field_type().fv_get_tag_type(),'hash'); 
                        echo '</table><div class="left"><input type="submit" value="Press before leaving this page to save your changes" /> </div></form>';
                }
                else
                {
                	echo '<p><b>No posts found!</b></p>';
                }
        }
        elseif ($description_tags_type == 'categories')
        {

                $categories = get_categories();//'post','','ID','asc',false,false,true,'','','');

                $category_name;

                foreach ($categories as $category){
                  $category_name[$category->cat_ID] = $category->cat_name;
                }

                if  (!empty($search_value))
                {
            	
                	$category_name_new;
                	foreach ($category_name as $key => $value)
                	{
                		if(stripos($value,$search_value)!==FALSE) {
                		   $category_name_new[$key] = $category_name[$key];
                		}
                	}
                  
                	$category_name = $category_name_new;
                	
                	foreach($categories AS $key => $value) {
                	    if(!isset($category_name[$value->cat_ID]))
                	       unset($categories[$key]);
                   }
                }

                $element_count = count($categories);
                if(isset($_POST['fv-items-per-page'])){?>
		  <form name="hidden_form">
		     <input type="hidden" name="page" value="fv_descriptions">
		     <input type="hidden" name="description_tags_type" value="<?php if($description_tags_type){echo $description_tags_type;}else{echo "pages" ;} ?>">
		     <input type="hidden" name="description_field_type" value="<?php echo fv_get_field_type();?>">
		     <input type="hidden" name="page_no" value="<?php echo get_last_page_no($element_count); ?>">
		  </form>
		  <script>
		  document.hidden_form.submit();
		  </script>		  
		<?php }
                if (($element_count > get_option( 'fv_items_per_page' )) and (($page_no != 'all') or empty($page_no)))
                {
                	if($page_no > 1)
                	{
                		$categories = array_splice($categories, ( ( $page_no - 1 ) * get_option( 'fv_items_per_page' )));
                	}

                	$categories = array_slice($categories, 0, get_option( 'fv_items_per_page' ));
                }
                ?>                           
                <div class="tablenav top">
                  <div class="tablenav-pages" style="line-height: 10px;">
		     <span class="pagination-links">
			<span class="displaying-num">
                        <center>Displaying <?php echo ($page_no - 1) * get_option( 'fv_items_per_page' ) + 1; ?> -
                        <?php 
                        if ( ( $page_no * get_option( 'fv_items_per_page' ) ) > $element_count )
                        echo $element_count;
                        else echo $page_no * get_option( 'fv_items_per_page' ); 
                        ?> of <?php
			echo $element_count.' ';
			if($element_count>1){
			   echo "items";
			}else{
			   echo "item";
			}?>
			</center> 
			</span>
		     </span>
		     <span class="pagination-links">		   
			<form method="GET">
                      <?php 
                      
		      			
			$max_page=ceil($element_count/get_option('fv_items_per_page'));
			if($page_no>$max_page) {
			   $page_no=$max_page;
			}
			
			
                      
		      
		      if ( ( $page_no * get_option( 'fv_items_per_page' ) ) < $element_count){
			if($page_no<1){
			  $page_no=1;
			  }
		      }
			$prev_page=$page_no-1;
                        $next_page=$page_no+1;
			
		      if ($page_no > 1)			
		      echo '<a class="prev-page" href="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&page_no='.$prev_page.'&description_tags_type='.$description_tags_type.$search_query_string.'">&laquo;</a>'; ?>
		      <?php
			if(get_option( 'fv_items_per_page' )>= $element_count){
			echo '</div></div>';
			}else{
			?>
			      <input type="hidden" name="page" value="fv_descriptions">
			      <input type="hidden" name="description_tags_type" value="<?php if($description_tags_type){echo $description_tags_type;}else{echo "pages" ;} ?>">
			      <input type="hidden" name="description_field_type" value="<?php echo fv_get_field_type();?>">
			      <input type="hidden" name="page_no" value="<?php echo $page_no;?>">
			      Go to page:
			      <input type="number" style="width: <?php echo get_style_width_listing_input($max_page).'px' ; ?>;" name="page_no" value="<?php echo $page_no;?>"  max="<?php echo $max_page; ?>" min="1">			      
			      <input type="submit" value="GO">
			      
		      
                      <?php if ( ( $page_no * get_option( 'fv_items_per_page' ) ) < $element_count)
		      echo '<a class="next-page" href="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&page_no='.$next_page.'&description_tags_type='.$description_tags_type.$search_query_string.'">&raquo;</a>';
		      ?>   
			</form>			
		     </span> 
                  </div>
                </div>
		
                <?php
		     }
		     
                if($categories) {
                ?>
				<form name="categories-form" action="<?php echo $_SERVER['REQUEST_URI'].'&page_no='.$page_no; ?>" method="post">
				<div class="left"><input type="submit" value="Press before leaving this page to save your changes" /> </div><div class="clearer"></div>
                <input type="hidden" name="action" value="categories" />
                <table class="widefat">
                <thead>
                <tr>
                <th scope="col" width="70">ID</th>
                <th scope="col" width="250">Category</th>
                <th scope="col">Description</th>
                </tr>
                </thead>
                <tbody>
                <?php

                foreach ($categories as $category)
                {
						$category_value = $category->category_description;
						
						if (get_magic_quotes_runtime())
						{
							$category_value = stripslashes($category_value);
						}
                        ?>
                        <tr>
                        <td><a href="<?php echo get_category_link($category->cat_ID) ?>"><?php echo $category->cat_ID ?></a></td>
                        <?php if(fv_get_field_type() == 'title' or fv_get_field_type() == 'bothatonce') : ?>
                        <td><input  type="text" name="title_<?php echo $category->cat_ID ?>" value="<?php echo $category->cat_name ?>" /></td>
                        <?php else : ?>
                        <td><?php echo $category->cat_name ?></td>
                        <?php endif; ?>
                         <?php if(fv_get_field_type() == 'description' or fv_get_field_type() == 'bothatonce') : ?>
                        <td><input type="text" name="description_<?php echo $category->cat_ID ?>" value="<?php echo $category_value; ?>" size="70" /></td>
                        <?php else : ?>
                        <td><?php echo $category_value; ?></td>
                        <?php endif; ?>
                        <?php
                }
                wp_nonce_field('fv_'.fv_get_field_type().fv_get_tag_type(),'hash'); 
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
                
                                
                <?php if(fv_get_field_type() == 'title' or fv_get_field_type() == 'all3atonce' or fv_get_field_type() == 'bothatonce') : ?>
                <td><input type="text"  title="<?php echo htmlspecialchars( $element->post_description ); ?>" name="tagtitle_<?php echo $element->ID ?>" id="tagtitle_<?php echo $element->ID ?>" value="<?php echo $pad.$element->post_title ?>"></td>
                <?php else : ?>
                 <td><?php echo $pad.$element->post_title ?></td>
                <?php endif; ?>
                
                
                <?php if($fieldname=='excerpt') : ?>
                
                  <?php if(fv_get_field_type() == 'description' or fv_get_field_type() == 'all3atonce' or fv_get_field_type() == 'bothatonce') : ?>                   
                <td><input type="text" title="<?php echo htmlspecialchars( $element->post_description ); ?>" name="tagdescription_<?php echo $element->ID ?>" id="tagdescription_<?php echo $element->ID ?>" value="<?php echo htmlspecialchars ($element_value); ?>" size="80" /></td>
                  <?php else : ?>
                <td><?php echo htmlspecialchars ($element_value); ?></td>
                  <?php endif; ?>
                  
                <?php else : ?>
                
                   <?php if(fv_get_field_type() == 'description' or fv_get_field_type() == 'all3atonce' or fv_get_field_type() == 'bothatonce') : ?>
                <td><input type="text" title="<?php echo htmlspecialchars( trim(stripcslashes(get_post_meta($element->ID, $fieldname, true))) ); ?>" name="tagdescription_<?php echo $element->ID ?>" id="tagdescription_<?php echo $element->ID ?>" value="<?php echo htmlspecialchars( trim(stripcslashes(get_post_meta($element->ID, $fieldname, true))) ); ?>" size="80" /></td>
                <?php else : ?>
                <td><?php echo htmlspecialchars( trim(stripcslashes(get_post_meta($element->ID, $fieldname, true))) ); ?></td>                  
                <?php endif; ?>
                
                <?php endif; ?>

              <?php global $description_tags_type;  
              if ($description_tags_type == 'posts') { ?>
                <?php if(fv_get_field_type() == 'keywords' or fv_get_field_type() == 'all3atonce') : ?>
                <td><input type="text" <?php if(fv_get_field_type() == 'all3atonce') echo 'size="20"'; ?> title="<?php echo htmlspecialchars( $element->post_description ); ?>" name="tagkeywords_<?php echo $element->ID ?>" id="tagkeywords_<?php echo $element->ID ?>"
			   value="<?php
			   $keywords=get_the_tags($element->ID);
			   for($i=0;$i<count($keywords);$i++){
			    if($i===count($keywords)-1) echo $keywords[$i]->name;
			    else echo $keywords[$i]->name.',';  
			   }
			  ?>"></td>
                <?php else : ?>
                 <td>
		  <?php
			   $keywords=get_the_tags($element->ID);
			   for($i=0;$i<count($keywords);$i++){
			    if($i===count($keywords)-1) echo $keywords[$i]->name;
			    else echo $keywords[$i]->name.','; 
			   }
		  ?>
		 </td>
                <?php endif; ?>
              <?php } ?>  
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

function get_style_width_listing_input($max_page){
      if($max_page-1 <10){
	 return 40;
      }elseif($max_page-1>=10 && $max_page-1<100){
	 return 50;		   
      }
      
      return 60;
}

function get_last_page_no($count){
	 $current_page = $_GET['page_no'];
	 
	 
	 if($current_page * $_POST['fv-items-per-page'] > $count ){	    
	    $last_page = ceil($count/get_option( 'fv_items_per_page' ));
	 }else{
	    $last_page = $_GET['page_no'];
	 }
	    
	 return $last_page;
      
}

?>
