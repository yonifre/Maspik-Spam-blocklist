<?php

namespace IdeoLogix\DigitalLicenseManagerClient\Utils;

/**
 * Class FileSystem
 * @package IdeoLogix\DigitalLicenseManagerClient\Utils
 */
class FileSystem {

	/**
	 * Mkdir -p
	 * @param $target
	 *
	 * @return bool
	 */
	public static function mkdir_p( $target ) {
		$wrapper = null;

		// From php.net/mkdir user contributed notes.
		$target = str_replace( '//', '/', $target );

		// Put the wrapper back on the target.
		if ( null !== $wrapper ) {
			$target = $wrapper . '://' . $target;
		}

		/*
		 * Safe mode fails with a trailing slash under certain PHP versions.
		 */
		$target = rtrim( $target, '/' );
		if ( empty( $target ) ) {
			$target = '/';
		}

		if ( file_exists( $target ) ) {
			return @is_dir( $target );
		}

		// Do not allow path traversals.
		if ( false !== strpos( $target, '../' ) || false !== strpos( $target, '..' . DIRECTORY_SEPARATOR ) ) {
			return false;
		}

		// We need to find the permissions of the parent folder that exists and inherit that.
		$target_parent = dirname( $target );
		while ( '.' !== $target_parent && ! is_dir( $target_parent ) && dirname( $target_parent ) !== $target_parent ) {
			$target_parent = dirname( $target_parent );
		}

		// Get the permission bits.
		$stat = @stat( $target_parent );
		if ( $stat ) {
			$dir_perms = $stat['mode'] & 0007777;
		} else {
			$dir_perms = 0777;
		}

		if ( @mkdir( $target, $dir_perms, true ) ) {

			/*
			 * If a umask is set that modifies $dir_perms, we'll have to re-set
			 * the $dir_perms correctly with chmod()
			 */
			if ( ( $dir_perms & ~umask() ) != $dir_perms ) {
				$folder_parts = explode( '/', substr( $target, strlen( $target_parent ) + 1 ) );
				for ( $i = 1, $c = count( $folder_parts ); $i <= $c; $i++ ) {
					chmod( $target_parent . '/' . implode( '/', array_slice( $folder_parts, 0, $i ) ), $dir_perms );
				}
			}

			return true;
		}

		return false;
	}

}
