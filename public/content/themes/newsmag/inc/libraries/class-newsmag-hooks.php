<?php
if ( ! defined( 'WPINC' ) ) {
	die;
}
/**
 * Class Newsmag_Hooks
 */
class Newsmag_Hooks {
	/**
	 * Newsmag_Hooks constructor.
	 */
	public function __construct() {
		/**
		 * Plyr layout for videos in content
		 */
		add_filter( 'the_content', array( $this, 'add_plyr_layout' ) );
		/**
		 * Fix responsive videos
		 */
		add_filter( 'embed_oembed_html', array( $this, 'fix_responsive_videos' ), 10, 3 );
		add_filter( 'video_embed_html', array( $this, 'fix_responsive_videos' ) );
		/**
		 * Ajax request to retrieve Attachment Image
		 */
		add_action( 'wp_ajax_newsmag_get_attachment_image', array( $this, 'get_attachment_image' ) );
		add_action( 'wp_ajax_nopriv_newsmag_get_attachment_image', array( $this, 'get_attachment_image' ) );
		/**
		 * Custom body classes
		 */
		add_filter( 'body_class', array( $this, 'body_classes' ) );

		/**
		 * Flush the category transient on category edit or post save
		 */
		add_action( 'edit_category', array( $this, 'category_transient_flusher' ) );
		add_action( 'save_post', array( $this, 'category_transient_flusher' ) );
		/**
		 * Change title of archive
		 */
		add_filter( 'get_the_archive_title', array( $this, 'remove_from_archive_title' ) );
		/**
		 * Add a <span> html tag to the category item
		 */
		add_filter( 'wp_list_categories', array( $this, 'add_span_cat_count' ) );
		add_filter( 'get_archives_link', array( $this, 'add_span_archive_count' ) );
	}

	/**
	 * Filter the categories widget to add a <span> element before the count
	 *
	 * @param $links
	 *
	 * @return mixed
	 */
	public function add_span_cat_count( $links ) {
		$links = str_replace( '</a> (', '</a> <span class="newsmag-cat-count">', $links );
		$links = str_replace( ')', '</span>', $links );

		return $links;
	}

	/**
	 * Filter the archives widget to add a <span> element before the count
	 *
	 * @param $links
	 *
	 * @return mixed
	 */
	public function add_span_archive_count( $links ) {
		$links = str_replace( '</a>&nbsp;(', '</a> <span class="newsmag-cat-count">', $links );
		$links = str_replace( ')', '</span>', $links );

		return $links;
	}

	/**
	 * @param $title
	 *
	 * @return string|void
	 */
	public function remove_from_archive_title( $title ) {
		if ( is_category() ) {

			$title = single_cat_title( '', false );

		} elseif ( is_tag() ) {

			$title = single_tag_title( '', false );

		} elseif ( is_author() ) {

			$title = '<span class="vcard">' . get_the_author() . '</span>';

		}

		return $title;
	}

	/**
	 * Flush out the transients used in newsmag_categorized_blog.
	 */
	public function category_transient_flusher() {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		// Like, beat it. Dig?
		delete_transient( 'newsmag_categories' );
	}

	/**
	 * Adds custom classes to the array of body classes.
	 *
	 * @param array $classes Classes for the body element.
	 *
	 * @return array
	 */
	public function body_classes( $classes ) {
		// Adds a class of group-blog to blogs with more than 1 published author.
		if ( is_multi_author() ) {
			$classes[] = 'group-blog';
		}

		// Adds a class of hfeed to non-singular pages.
		if ( ! is_singular() ) {
			$classes[] = 'hfeed';
		}

		return $classes;
	}


	/**
	 * @param $content
	 *
	 * @return mixed|string
	 */
	public function add_plyr_layout( $content ) {
		if ( ! is_single() ) {
			return $content;
		}

		// has normal video
		// remove video tag
		preg_match( '/\[video.*?\]/', $content, $matches );
		if ( ! empty( $matches ) ) {
			preg_match_all( '#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', $matches[0], $match );
			if ( ! empty( $match ) && filter_var( $match[0][0], FILTER_VALIDATE_URL ) ) {
				$content = preg_replace( '/\[video.*?\]/', '', $content );
				$content = preg_replace( '/\[\/video.*?\]/', '', $content );

				$html  = '<div>';
				$html .= '<video class="plyr">';
				$html .= '<source src=' . $match[0][0] . '>';
				$html .= '</video>';
				$html .= '</div>';

				return $html . $content;
			}
		};

		return $content;
	}

	/**
	 * @param $html
	 *
	 * @return string
	 */
	public function fix_responsive_videos( $html ) {
		return '<div class="newsmag-video-container">' . $html . '</div>';
	}

	/**
	 *
	 */
	public function get_attachment_image() {
		$id   = intval( $_POST['attachment_id'] );
		$size = esc_html( $_POST['attachment_size'] );

		$src = wp_get_attachment_image( $id, false );

		echo $src;
		die();
	}
}
