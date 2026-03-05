<?php
/**
 * Features Class
 *
 * Handles feature functionality: shortcodes (year, social sharing) and
 * automatic social sharing on content when enabled via the settings page.
 *
 * @package asc-core-tools
 * @since 1.0.0
 */

declare( strict_types = 1 );

namespace ASC\CoreTools\Admin;

use ASC\CoreTools\Core\Core;

/**
 * Features Class
 *
 * Registers shortcodes and the_content filter only when the corresponding
 * settings are enabled (social sharing). The year shortcode is always registered.
 */
class Features {

	/**
	 * Initialize the Features class.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Shortcode to display the current year (e.g. for copyright footers).
	 *
	 * @return string Current 4-digit year.
	 */
	public function shortcode_year(): string {
		return (string) gmdate( 'Y' );
	}

	/**
	 * Build HTML for the social sharing bar (shared by shortcode and the_content).
	 *
	 * @param string $link  URL to share.
	 * @param string $title Title or link text for the share.
	 * @return string HTML markup for the share container.
	 */
	public static function social_sharing( string $link, string $title ): string {
		$settings = Core::get_settings();
		$enc_link = urlencode( $link );
		$enc_title = urlencode( $title );
		$subject = rawurlencode( htmlspecialchars_decode( $title, ENT_QUOTES | ENT_HTML5 ) );
		$attr_link = esc_attr( $link );

		$fb_url = 'https://www.facebook.com/sharer.php?u=' . $enc_link . '&amp;t=' . $enc_title;
		$x_url = 'https://twitter.com/intent/tweet?text=' . $enc_title . '&amp;url=' . $enc_link;
		$li_url = 'https://www.linkedin.com/sharing/share-offsite/?url=' . $enc_link;
		$bluesky_text = $title . ' ' . $link;
		$bluesky_url = 'https://bsky.app/intent/compose?text=' . rawurlencode( $bluesky_text );
		$mail_url = 'mailto:?subject=' . $subject . '&body=' . rawurlencode( $link );

		$html = '<div class="asc-core-tools-share-container" role="region" aria-label="' . esc_attr__( 'Share this', 'asc-core-tools' ) . '">';
		$html .= '<div class="asc-core-tools-share-networks">';
		$html .= '<span class="asc-core-tools-share-header">Share:</span>';
		if ( ! empty( $settings['share_linkedin'] ) ) {
			$html .= '<a class="asc-core-tools-share-icon" href="' . esc_url( $li_url ) . '" target="_blank" rel="noopener noreferrer nofollow" title="LinkedIn" aria-label="' . esc_attr__( 'Share on LinkedIn', 'asc-core-tools' ) . '"><i class="fab fa-linkedin-in" aria-hidden="true"></i></a>';
		}
		if ( ! empty( $settings['share_facebook'] ) ) {
			$html .= '<a class="asc-core-tools-share-icon" href="' . esc_url( $fb_url ) . '" target="_blank" rel="noopener noreferrer nofollow" title="Facebook" aria-label="' . esc_attr__( 'Share on Facebook', 'asc-core-tools' ) . '"><i class="fab fa-facebook-f" aria-hidden="true"></i></a>';
		}
		if ( ! empty( $settings['share_bluesky'] ) ) {
			$html .= '<a class="asc-core-tools-share-icon" href="' . esc_url( $bluesky_url ) . '" target="_blank" rel="noopener noreferrer nofollow" title="Bluesky" aria-label="' . esc_attr__( 'Share on Bluesky', 'asc-core-tools' ) . '"><i class="fab fa-bluesky" aria-hidden="true"></i></a>';
		}
		if ( ! empty( $settings['share_x'] ) ) {
			$html .= '<a class="asc-core-tools-share-icon" href="' . esc_url( $x_url ) . '" target="_blank" rel="noopener noreferrer nofollow" title="X" aria-label="' . esc_attr__( 'Share on X', 'asc-core-tools' ) . '"><i class="fab fa-x-twitter" aria-hidden="true"></i></a>';
		}
		if ( ! empty( $settings['share_email'] ) ) {
			$html .= '<a class="asc-core-tools-share-icon" href="' . esc_url( $mail_url ) . '" target="_self" rel="noopener noreferrer nofollow" title="Email" aria-label="' . esc_attr__( 'Share via email', 'asc-core-tools' ) . '"><i class="far fa-envelope" aria-hidden="true"></i></a>';
		}
		if ( ! empty( $settings['share_copy_link'] ) ) {
			$html .= '<button type="button" class="asc-core-tools-share-icon asc-core-tools-copy" title="Copy Link" aria-label="' . esc_attr__( 'Copy link', 'asc-core-tools' ) . '" data-clipboard-text="' . $attr_link . '"><i class="far fa-copy" aria-hidden="true"></i><span class="asc-core-tools-share-success" aria-live="polite" aria-atomic="true"></span></button>';
		}
		$html .= '</div>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Shortcode callback: output social sharing bar for the current post.
	 *
	 * @return string HTML for the sharing bar or empty string if no post.
	 */
	public function shortcode_social_sharing(): string {
		$post = get_post();
		if ( ! $post ) {
			return '';
		}
		return self::social_sharing( get_permalink( $post->ID ), $post->post_title );
	}

	/**
	 * Filter the_content: append social sharing bar when enabled and post type is in the list.
	 *
	 * @param string $content Existing post content.
	 * @return string Content with optional sharing bar appended.
	 */
	public function the_content_social_sharing( string $content ): string {
		if ( is_admin() || ! is_main_query() || ! in_the_loop() || is_feed() ) {
			return $content;
		}

		$settings = Core::get_settings();
		if ( empty( $settings['enable_social_sharing'] ) ) {
			return $content;
		}

		$post = get_post();
		if ( ! $post ) {
			return $content;
		}

		$post_types_str = isset( $settings['social_sharing_post_types'] ) ? $settings['social_sharing_post_types'] : 'post,page';
		$post_types = array_filter( array_map( 'trim', explode( ',', $post_types_str ) ) );
		if ( empty( $post_types ) ) {
			return $content;
		}

		if ( ! in_array( $post->post_type, $post_types, true ) ) {
			return $content;
		}

		return $content . self::social_sharing( get_permalink( $post->ID ), $post->post_title );
	}

	/**
	 * Initialize features: register shortcodes and hooks only when enabled.
	 *
	 * Year shortcode is always registered. Social sharing shortcode and
	 * the_content filter are only added when enable_social_sharing is set.
	 *
	 * @return void
	 */
	private function init(): void {
		$settings = Core::get_settings();

		add_shortcode( 'asc_core_tools_year', array( $this, 'shortcode_year' ) );

		if ( ! empty( $settings['enable_social_sharing'] ) ) {
			add_shortcode( 'asc_core_tools_social_sharing', array( $this, 'shortcode_social_sharing' ) );
			add_filter( 'the_content', array( $this, 'the_content_social_sharing' ), 10, 1 );
		}
	}
}
