<?php

/**
* Resource Library
*
* @package new42
*/

get_header();

// Sort SearchWP Post, Page, and Custom Post Type Results by date in DESC order.
add_filter( 'searchwp\query\mods', function( $mods, $query ) {

	if ( ! isset( $_GET['sort'] ) || $_GET['sort'] !== 'recent' ) {
		return $mods;
	}

	foreach ( $query->get_engine()->get_sources() as $source ) {
		$flag = 'post' . SEARCHWP_SEPARATOR;
		if ( 'post.' !== substr( $source->get_name(), 0, strlen( $flag ) ) ) {
			continue;
		}

		$mod = new \SearchWP\Mod( $source );

		$mod->order_by( function( $mod ) {
			return $mod->get_local_table_alias() . '.post_date';
		}, 'DESC', 9 );

		$mods[] = $mod;
	}

	return $mods;
}, 20, 2 );

// Sort SearchWP Post, Page, and Custom Post Type Results by post title stored in a custom field in ASC order.
add_filter( 'searchwp\query\mods', function( $mods, $query ) {

	if ( isset( $_GET['sort'] ) && $_GET['sort'] !== 'alphabetical' ) {
		return $mods;
	}

	global $wpdb;

	$mod = new \SearchWP\Mod();

	$mod->set_local_table( $wpdb->postmeta );
	$mod->on( 'post_id',  [ 'column' => 'id' ] );
	$mod->on( 'meta_key', [ 'value'  => 'clean_title' ] );

	$mod->order_by( function( $mod ) {
		// Order the results by alphanumeric values
		return $mod->get_local_table_alias() . '.meta_value';

		// Use the line below instead for numeric values
		// return $mod->get_local_table_alias() . '.meta_value+0';
	}, 'ASC', 5 );

	$mods[] = $mod;

	return $mods;
}, 20, 2 );

// Disable SearchWP's exact match buoy when performing partial match logic.
add_filter( 'searchwp\query\partial_matches\buoy', '__return_false' );

?>
	<main class="page">
		<div class="container mt-32 lg:mt-16">
			<?php
			$featured_resources_heading = get_field('featured_resources_heading', 'option');
			if ( $featured_resources_heading ) {
				echo $featured_resources_heading;
			} else {
				echo '<h1>Resource Library</h1>';
			}?>

			<?php
			the_field( 'resource_library_content', 'option' );
			?>

		</div>

		<div id="resources-results" class="container grid grid-cols-12 mt-16 lg:gap-12 md:flex md:flex-col md:gap-0">
			<div class="col-span-3 mb-14 lg:col-span-4">
				<div>
					<form role="search" method="get" class="search-form search-form-side" action="">
						<label>
							<span class="screen-reader-text">Search for:</span>
							<input type="search" class="search-field-side" placeholder="Keyword search" value="<?php if ( isset( $_GET['search'] ) ) echo $_GET['search']; ?>" name="s">
						</label>
					</form>
				</div>

				<div class="flex items-center justify-between pt-0 pb-5 border-b border-[#002639]">
					<p class="h4 m-0">Filters</p>
					<p class="clear-all main cursor-pointer text-sm underline p-0 m-0">Clear All</p>
				</div>

				<?php
				$taxonomies = get_object_taxonomies( 'resource', 'objects' );
				$tax_query = array( 'relation' => 'AND' );
				$tax_slugs = array();

				foreach ( $taxonomies as $taxonomy_slug => $taxonomy ):
					$tax_slugs[] = $taxonomy_slug;


					if (
						$taxonomy_slug !== 'post_tag' &&
						$taxonomy_slug !== 'national-arts-standard'
					):
						?>
						<div class="filter-section flex flex-col py-4 border-b border-[#002639]" data-taxonomy="<?php echo $taxonomy_slug; ?>">

							<?php
							$tags = array();
							if ( isset( $_GET[$taxonomy_slug] ) ) $tags = explode( ',', $_GET[$taxonomy_slug] );

							$args = array(
								'taxonomy' => $taxonomy_slug,
								'hide_empty' => false,
								'orderby' => 'name',
								'order' => 'ASC'
							);
							$terms = get_terms( $args );
							?>
							<input id="filter-<?php echo $taxonomy_slug; ?>" type="checkbox" class="absolute left-[-9999px] opacity-0 appearance-none invisible pointer-events-none"<?php if ( isset( $_GET[$taxonomy_slug] ) ) echo ' checked'; ?>>
							<label for="filter-<?php echo $taxonomy_slug; ?>" class="relative cursor-pointer mb-0"><span class="font-semibold"><?php echo $taxonomy->label; ?></span> </label>

							<div class="hidden flex-col gap-3 mt-4">
								<div class="filter-input" data-term="select-all">
									<input id="<?php echo $taxonomy_slug; ?>-all" type="checkbox" class="absolute left-[-9999px] opacity-0 appearance-none invisible pointer-events-none">
									<label for="<?php echo $taxonomy_slug; ?>-all" class="flex items-start justify-start gap-2 text-[16px] leading-[1.3] m-0">Select All</label>
								</div>

								<?php
								foreach ($terms as $term):
									?>
									<div class="filter-input" data-term="<?php echo esc_attr($term->slug); ?>">
										<input id="<?php echo $taxonomy_slug . '-' . esc_attr($term->slug); ?>" type="checkbox" class="absolute left-[-9999px] opacity-0 appearance-none invisible pointer-events-none" value="<?php echo esc_attr($term->slug); ?>"<?php if (in_array($term->slug, $tags)) echo ' checked'; ?>>
										<label for="<?php echo $taxonomy_slug . '-' . esc_attr($term->slug); ?>" class="flex items-start justify-start gap-2 text-[16px] leading-[1.3] m-0">
											<?php echo esc_html($term->name); ?>
										</label>
									</div>
								<?php
								endforeach;
								?>


								<p class="clear-all cursor-pointer text-sm underline p-0 mt-0 mb-3 ml-auto">Clear Selected</p>
							</div>
						</div>
					<?php
					endif;
				endforeach;
				?>

				<div class="flex flex-col gap-2 mt-8">
					<p>Share Results</p>

					<div class="flex items-center justify-start gap-2">
						<a href="" class="share link relative min-h-[32px] min-w-[32px] border border-black rounded-full transition-colors duration-200 hover:bg-black" aria-label="Copy the New Victory Education Resource Library link"></a>
						<a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo $permalink; ?>&title=<?php echo urlencode( 'New Victory Education Resource Library' ); ?>" class="share linkedin relative min-h-[32px] min-w-[32px] border border-black rounded-full transition-colors duration-200 hover:bg-black" aria-label="Share New Victory Education Resource Library on LinkedIn" target="_blank"></a>
						<a href="https://twitter.com/intent/tweet?url=<?php echo sanitize_text_field( esc_html( home_url( add_query_arg( [] ) ) ) ); ?>&text=<?php echo urlencode( 'New Victory Education Resource Library' ); ?>" class="share twitter relative min-h-[32px] min-w-[32px] border border-black rounded-full transition-colors duration-200 hover:bg-black" aria-label="Share New Victory Education Resource Library on X (formerly Twitter)" target="_blank"></a>
						<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo sanitize_text_field( esc_html( home_url( add_query_arg( [] ) ) ) ); ?>&quote=<?php echo urlencode( 'New Victory Education Resource Library' ); ?>" class="share facebook relative min-h-[32px] min-w-[32px] border border-black rounded-full transition-colors duration-200 hover:bg-black" aria-label="Share New Victory Education Resource Library on Facebook" target="_blank"></a>
					</div>
				</div>
			</div>

			<div class="col-span-1 lg:hidden"></div>
			<div class="col-span-8 lg:col-spam-8">
				<div id="records" class="col-span-8 lg:col-span-8">
					<div class="async flex items-center justify-between mb-5 xs:flex-col xs:items-start">

						<?php

						$tax_query = array('relation' => 'AND'); // Change made here

						foreach ( $_GET as $taxonomy => $terms ):
							if ( in_array( $taxonomy, $tax_slugs ) ):
								$terms_array = explode( ',', $terms );
								$tax_query[] = array(
									'taxonomy' => $taxonomy,
									'field' => 'slug',
									'terms' => $terms_array,
									'operator' => 'IN' // Change made here
								);
							endif;
						endforeach;

						$meta_query = array();

						$args = array(
							'post_type' => 'resource',
							'posts_per_page' => isset( $_GET['records'] ) ? $_GET['records'] : 8,
							'meta_key' => 'clean_title', // Sort by clean title
							'orderby' => 'meta_value',
							'order' => 'ASC',
							'tax_query' => $tax_query,
							'meta_query' => $meta_query,
							'ignore_sticky_posts' => 1
						);


						if ( isset( $_GET['search'] ) ):
							$args['s'] = sanitize_text_field( $_GET['search'] );
						endif;

						if ( isset( $_GET['sort'] ) ):
							switch ( $_GET['sort'] ):
								case 'recent':
									$args['orderby'] = 'date';
									$args['order'] = 'DESC';

									break;
								case 'alphabetical':
									$args['orderby'] = 'clean_title';
									$args['order'] = 'ASC';

									break;
							endswitch;
						endif;

						// Prepare the search query
						$search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

						// Prepare arguments for SearchWP Query
						$swp_args = [
							's' => $search_query,
							'engine' => 'default', // or your custom engine name
							'post_type' => 'resource',
							'posts_per_page' => isset($_GET['records']) ? intval($_GET['records']) : 8,
						];

						// Add tax_query if it exists in $args
						if (!empty($args['tax_query'])) {
							$swp_args['tax_query'] = $args['tax_query'];
						}

						// Add sorting
						if (isset($_GET['sort'])) {
							switch ($_GET['sort']) {
								case 'recent':
									$swp_args['orderby'] = 'date';
									$swp_args['order'] = 'DESC';
									break;
								case 'alphabetical':
									$swp_args['orderby'] = 'clean_title';
									$swp_args['order'] = 'ASC';
									break;
							}
						}

						// Log the query arguments
						error_log('SearchWP Query Args: ' . print_r($swp_args, true));

						// Create the SWP_Query
						$records = new SWP_Query($swp_args);

						// Get the total number of results
						$records_total = $records->found_posts;

						// Log the results
						error_log('SearchWP Results Count: ' . count($records->posts));
						error_log('SearchWP Total Found: ' . $records_total);

						if ( !isset( $_GET['sort'] ) || $_GET['sort'] === 'alphabetical' ) $sorted = 'Alphabetical';
						else $sorted = 'Recent';

						$records_current = ( $_GET['records'] ) ? $_GET['records'] : 8;
						$records_total = $records->found_posts;

						if ( isset( $_GET['records'] ) ):
							$records_count = ( $_GET['records'] > $records_total ) ? $records_total : $_GET['records'];
						else:
							$records_count = ( $records_total < 8 ) ? $records_total : 8;
						endif;

						if ( isset( $_SERVER['QUERY_STRING'] ) && !empty( $_SERVER['QUERY_STRING'] ) ) $_SESSION['query_string'] = $_SERVER['QUERY_STRING'];
						?>

						<p class="text-lg leading-none mt-0">Showing <span id="records-show"><?php echo $records_count; ?></span> out of <span id="records-total"><?php echo $records_total; ?></span> results</p>

						<div class="flex items-center justify-end gap-2 pt-0">
							<p class="text-[14px] w-fit mt-0">Sort by</p>

							<div class="flex items-center justify-end gap-2">
								<div id="sort-records-trigger" class="relative flex items-center justify-end gap-[6px] cursor-pointer font-semibold">
									<span class="leading-none"><?php echo $sorted; ?></span>

									<select id="sort-records" class="absolute top-0 left-0 h-full w-full opacity-0">
										<option value="recent"<?php if ( $sorted === 'Recent' ) echo ' selected'; ?>>Recent</option>
										<option value="alphabetical"<?php if ( $sorted === 'Alphabetical' ) echo ' selected'; ?>>Alphabetical</option>
									</select>
								</div>
							</div>
						</div>
					</div>

					<div class="async filters">

						<?php
						if ( isset( $_GET ) ):
							$term_count = 1;
							$term_active = false;
							$excluded = array(
								'post_type',
								'records',
								'search',
								'sort'
							);

							foreach ( $_GET as $taxonomy => $term ):
								if ( !in_array( $taxonomy, $excluded ) ):
									$params = explode( ',', $term );

									if ( $term_count === 1 ) echo '<div class="filters__inner">';

									foreach ( $params as $param ):
										if ( $param !== 'on' ) echo '<label for="' . $taxonomy . '-' . esc_html( $param ) . '" class="filters__inner__filter">' . esc_html( get_term_by( 'slug', $param, $taxonomy )->name ) . '</label>';
									endforeach;

									$term_count++;
								endif;
							endforeach;

							if ( $term_active === true ) echo '</div>';
						endif;
						?>

					</div>

					<?php
					if ( $records->have_posts() ):
						?>
						  <div class="async records">
                        <div class="mb-4">
                            <div class="async__inner grid grid-cols-2 gap-x-8 gap-y-12 base:grid-cols-1 md:grid-cols-2 sm:grid-cols-1" data-current="<?php echo $records_current; ?>" data-total="<?php echo $records->found_posts; ?>">

                                <?php
                                    while ( $records->have_posts() ):
                                        $records->the_post();
                                        $cover_image = get_field( 'cover_image' );
                                        $allowed_tags = array(
                                            'i' => array(),
                                            'span' => array(),
                                        );                                        
                                        $title = wp_kses( get_the_title(), $allowed_tags); // Keep <i> and <span>but remove <p> tags that might have been added
										$title = preg_replace('#<p[^>]*>(\s|&nbsp;)*<\/p>#', '', $title);
                                        $content_type = wp_get_post_terms( get_the_ID(), 'content-type', array( 'fields' => 'names' ) )[0];
                                        
                                        $description= wp_kses( get_the_excerpt(), $allowed_tags); // Keep <i> and <span>but remove <p> tags that might have been added
										$description = preg_replace('#<p[^>]*>(\s|&nbsp;)*<\/p>#', '', $description);
                                        $formatted_content_type = str_replace(' ', '-', strtolower($content_type));

                                ?>
                                <a href="<?php the_permalink(); ?>" class="records__record flex flex-col gap-2 bg-transparent">
                                    <img src="/wp-content/media/<?php echo $cover_image; ?>" alt="<?php echo strip_tags($title); ?>">

                                    <p class="title font-semibold text-[#2D6738] text-[24px] leading-[1.3] mt-4 mb-1 resource-title"><?php echo $title; ?></h3>
                                    <p class="font-semibold text-[#636467] text-[16px] m-0 <?php echo $formatted_content_type; ?>"><?php echo $content_type; ?></p>
                                    <p class="font-normal text-[#002639] text-[18px] line-clamp-5 mt-1 mb-0"><?php echo $description; ?></p>
                                </a>
                                <?php
                                    endwhile; 
    
                                    if ( $records_count < $records_total ):
                                ?>
                                <a id="load-more-records" href="#" class="flex items-center justify-center font-semibold h-[56px] w-fit pt-[2px] px-4 mt-8 mx-auto border-2 border-[#002639] transition-colors duration-200 hover:text-white hover:bg-[#002639]">Load More Resources</a>
                                <?php
                                    endif;
                                ?>

                            </div>
                        </div>
                    </div>
                    
                    <?php
                        endif;
                    ?>

                </div>
            </div>
            </div><!-- #resources-results -->
            <div class="container mt-32 lg:mt-16" style="padding: 0;">

                <?php
                    the_field( 'additional_content', 'option' );
                ?>
                </div>
        </main><!-- .page -->
<?php 
    get_footer();
