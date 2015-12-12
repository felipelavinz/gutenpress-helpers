<?php
/**
 * Array helper methods
 */
namespace GutenPress\Helpers;

use Underscore\Types as Underscore;

class Arrays extends Underscore\Arrays{
	public function __call( $name, $arguments ){
		$maybe_method = Underscore\Strings::toSnakeCase( $name );
		return call_user_func_array( array( $this, $maybe_method ), $arguments );
	}
	public static function __callStatic( $name, $arguments ){
		$maybe_method = Underscore\Strings::toSnakeCase( $name );
		return forward_static_call_array( array( 'Underscore\Types\Arrays', $maybe_method ), $arguments );
	}

	public static function filter_recursive( $array, $callback = null ) {
		foreach ( $array as &$value ) {
			if ( is_array( $value ) ) {
				$value = $callback === null ? static::filter_recursive( $value ) : static::filter_recursive( $value, $callback );
			}
		}
		return $callback === null ? array_filter( $array ) : array_filter( $array, $callback );
	}

	/**
	 * Determines if an array is associative.
	 *
	 * An array is "associative" if it doesn't have sequential numeric keys beginning with zero.
	 *
	 * @param  array   $array
	 * @return boolean
	 * @internal Copied from Laravel framework (from Taylor Otwell under MIT License)
	 * @link https://github.com/laravel/framework/blob/be7fbb60376bd61f07e9c637473e5b2cf7eebe5c/src/Illuminate/Support/Arr.php#L279-L292
	 */
	public static function is_assoc( array $array ){
		$keys = array_keys( $array );
		return array_keys( $keys) !== $keys;
	}

	/**
	 * Reverse a flattened array in its original form.
	 *
	 * @param  array  $array flattened array
	 * @param  string $glue  glue used in flattening
	 * @return array  the unflattened array
	 * @internal (Mostly) copied from Fuel framework (from the FuelPHP Development Team under MIT License)
	 * @link https://github.com/fuel/core/blob/6c48d4e63bea3c268c97f0cc085a15ef57d40032/classes/arr.php#L382-L422
	 */
	public static function reverse_flatten( array $array, $glue = '.' ){
		$return = array();
		foreach ( $array as $key => $value ) {
			if ( stripos( $key, $glue ) !== false ) {
				$keys = explode( $glue, $key );
				$temp =& $return;
				while ( count( $keys ) > 1 ){
					$key = array_shift( $keys );
					$key = is_numeric( $key ) ? (int) $key : $key;
					if ( ! isset( $temp[ $key ] ) OR ! is_array( $temp[$key] ) ) {
						$temp[ $key ] = array();
					}
					$temp =& $temp[ $key ];
				}
				$key = array_shift( $keys );
				$key = is_numeric( $key ) ? (int) $key : $key;
				$temp[ $key ] = $value;
			} else {
				$key = is_numeric( $key ) ? (int) $key : $key;
				$return[ $key ] = $value;
			}
		}
		return $return;
	}
}