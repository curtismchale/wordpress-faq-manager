// webpack.mix.js

let mix = require( 'laravel-mix' );

mix.minify( 'lib/js/faq.admin.js' )
	.minify( 'lib/js/faq.front.js' )
	.minify( 'lib/css/faq.admin.css' )
	.minify( 'lib/css/faq.front.css' );
