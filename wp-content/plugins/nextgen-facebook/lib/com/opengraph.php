<?php
/*
License: GPLv3
License URI: http://surniaulula.com/wp-content/uploads/license/gpl.txt
Copyright 2012-2013 - Jean-Sebastien Morisset - http://surniaulula.com/
*/

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'SucomOpengraph' ) ) {

	class SucomOpengraph {

		protected $p;

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			$this->p->debug->mark();
		}
	
		public function parse( $html ) {
			$doc = new DomDocument();		// since PHP v4.1.0
			$ret = @$doc->loadHTML( $html );	// suppress parsing errors
			$xpath = new DOMXPath( $doc );
			$query = '//*/meta[starts-with(@property, \'og:\')]';
			$metas = $xpath->query( $query );
			$rmetas = array();
			foreach ( $metas as $meta ) {
				$property = $meta->getAttribute('property');
				$content = $meta->getAttribute('content');
				$rmetas[$property] = $content;
			}
			return $rmetas;
		}
	}
}
?>
