<?php
/**
 * Attachment helper.
 *
 * Basically, a decorator for WordPress posts with "attachment" post type.
 * Exposes a few properties and methods that can be useful when dealing with
 * files, such as the file URL, file size, etc.
 */
namespace GutenPress\Helpers;

class Attachment{
	protected $attachment;
	protected $id;
	protected $url;
	protected $size;
	protected $title;
	protected $type;
	protected $subtype;
	protected $pathinfo;

	private static $sizes_bytes = array(
		'B',
		'KB',
		'MB',
		'GB',
		'TB',
		'PB',
		'EB',
		'ZB',
		'YB'
	);

	public function __construct( $id = null ){
		$this->attachment = get_post( $id );
		$this->id         = $this->attachment->ID;
		$this->url        = wp_get_attachment_url( $this->id );
		$this->title      = get_the_title( $this->attachment );
		$this->pathinfo   = (object) pathinfo( parse_url( $this->url, PHP_URL_PATH ) );
		list( $type, $subtype ) = explode( '/', $this->attachment->post_mime_type );
		$this->type    = $type;
		$this->subtype = $subtype;
	}

	public function __get( $key ){
		if ( $key == 'size' ){
			return $this->get_human_size();
		}
		return isset( $this->{$key} ) ? $this->{$key} : null;
	}

	public function __isset( $key ){

	}

	public function get_size_bytes(){
		if ( ! isset($this->size) ) {
			$this->size = $this->calculate_size();
		}
		return $this->size;
	}

	private function calculate_size(){
		$upload_dir = wp_upload_dir();
		$file_path  = get_post_meta( $this->id, '_wp_attached_file', true );
		$full_path  = trailingslashit( $upload_dir['basedir'] ) . $file_path;
		$_not_found = apply_filters('gutenpress_helpers_attachments_size_not_found', _x('(Not found)', 'attachment file not found', 'gutenpress') );
		if ( ! is_readable( $full_path ) )
			return $_not_found;
		return filesize( $full_path );
	}

	public function get_size( $unit = '' ){
		$unit = stroupper( $unit );
		$size = $this->get_size_bytes();
		$un_i = array_search( $unit, static::$sizes_bytes );
		for ( $i = 0; $i < $un_i; $i++ ){
			$size /= 1024;
		}
		$precision = apply_filters('gutenpress_helpers_attachments_size_precision', $un_i > 3 ? 3 : $un_i );
		return round( $size, $precision ) .' '. $unit;
	}

	public function get_human_size(){
		$size = $this->get_size_bytes();
		for ( $i = 0; $size > 1024 && isset( static::$sizes_bytes[$i+1] ); $i++ ) {
			$size /= 1024;
		}
		$precision = apply_filters('gutenpress_helpers_attachments_human_size_precision', $i > 3 ? 3 : $i );
		return round( $size, $precision ) .' '. static::$sizes_bytes[ $i ];
	}
}