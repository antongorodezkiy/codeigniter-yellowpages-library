<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	/**
	* Name:  YP
	* Author: antongorodezkiy@gmail.com
	* Description:  YellowPages configuration settings.
	* Source: https://github.com/antongorodezkiy/codeigniter-yellowpages-library
	*/

	/**
	 * API key
	 **/
	$config['yp_api_key']   = '';

	/**
	 * API link
	 * http://http://api2.yp.com/listings/v1/search
	 **/
	$config['yp_api_link']   = 'http://api2.yp.com/listings/v1/search';
		
		
	/**
	 * listingCount, maximum - 50
	 **/
	$config['listingCount'] = 50;
	