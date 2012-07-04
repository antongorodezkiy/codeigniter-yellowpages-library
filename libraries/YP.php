<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	/**
	* Name: YP
	* Author: antongorodezkiy@gmail.com
	* Description:  YellowPages Library.
	* Source: https://github.com/antongorodezkiy/codeigniter-yellowpages-library
	*/


	class YP
	{
		protected $_ci;
		protected $config;

		public $response;

		function __construct()
		{
			//initialize the CI super-object
			$this->_ci =& get_instance();

			//load config
			$this->config = $this->_ci->load->config('yp', TRUE);

		}

		
		/**
		 * Search
		 */
		public function search($term,$location)
		{
			$params = array(
				'term'  		=> $term,
				'searchloc'   	=> $location,
				'format' 		=> 'json'
			);

			return $this->request($params);
		}
		
		
		public function searchAll($term,$location, $limit = 0)
		{
			$params = array(
				'term'  		=> $term,
				'searchloc'   	=> $location,
				'format' 		=> 'json'
			);
			
			$ok = $this->request($params);
			$count = 0;
			
			if ($ok)
			{
				$results = $this->listings;
				
				for($pageNum = 1; $pageNum <= $this->pages; $pageNum++ )
				{
					if ($limit && $pageNum > $limit)
						break;
					
					$params['pageNum'] = $pageNum;
					$ok = $this->request($params);
					
					if ($ok)
					{
						$results = array_merge($results,$this->listings);
						$count += $this->count;
					}
				}
	
				$this->count = $count;
				$this->listings = $results;
				return $results;
			}
			else
				return false;
		}

	
		private function parse()
		{
			if (isset($this->response->searchResult))
				$this->searchResult = ( $this->response->searchResult->metaProperties->resultCode == 'Success' );
			else
			{
				$this->searchResult = ( $this->response->result->metaProperties->resultCode == 'Success' );
			}
			
			if($this->searchResult)
			{
				$this->count = $this->response->searchResult->metaProperties->listingCount;
				$this->total = $this->response->searchResult->metaProperties->totalAvailable;
				$this->didYouMean = $this->response->searchResult->metaProperties->didYouMean;
				$this->message = $this->response->searchResult->metaProperties->message;
				$this->pageNum = $this->response->searchResult->metaProperties->inputParams->pageNum;
				$this->term = $this->response->searchResult->metaProperties->inputParams->term;
				
				$this->pages = ceil($this->total/$this->config['listingCount']);
				
				if ($this->count)
					$this->listings = $this->response->searchResult->searchListings->searchListing;
				else
					$this->listings = array();
			}
			
		}


		private function request($vars = array())
		{
			$vars['key'] = $this->config['yp_api_key'];
			$vars['listingCount'] = $this->config['listingCount'];
			
			
			$fp = null;
			$tmpfile = "";
			$encoded = "";
			foreach($vars AS $key=>$value)
				$encoded .= "$key=".urlencode($value)."&";
			$encoded = substr($encoded, 0, -1);
	
			$path = '';
			$method = 'GET';
			// construct full url
			$url = $this->config['yp_api_link'].'/'.$path;
	
			// if GET and vars, append them
			if($method == "GET")
				$url .= (FALSE === strpos($path, '?')?"?":"&").$encoded;
	
			// initialize a new curl object
			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

			curl_setopt($curl, CURLOPT_USERAGENT, $this->_ci->input->server('HTTP_USER_AGENT'));
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
			switch(strtoupper($method)) {
				case "GET":
					curl_setopt($curl, CURLOPT_HTTPGET, TRUE);
					break;
				case "POST":
					curl_setopt($curl, CURLOPT_POST, TRUE);
					curl_setopt($curl, CURLOPT_POSTFIELDS, $encoded);
					break;
				case "PUT":
					// curl_setopt($curl, CURLOPT_PUT, TRUE);
					curl_setopt($curl, CURLOPT_POSTFIELDS, $encoded);
					curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
					file_put_contents($tmpfile = tempnam("/tmp", "put_"),
						$encoded);
					curl_setopt($curl, CURLOPT_INFILE, $fp = fopen($tmpfile,
						'r'));
					curl_setopt($curl, CURLOPT_INFILESIZE,
						filesize($tmpfile));
					break;
				case "DELETE":
					curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
					break;
				default:
					throw(new TwilioException("Unknown method $method"));
					break;
			}
	
			// send credentials
			//curl_setopt($curl, CURLOPT_USERPWD,
			 //   $pwd = "{$this->AccountSid}:{$this->AuthToken}");
	
			// do the request. If FALSE, then an exception occurred
			if(FALSE === ($result = curl_exec($curl)))
				die("Curl failed with error " . curl_error($curl));
	
			// get result code
			$responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

			// unlink tmpfiles
			if($fp)
				fclose($fp);
			if(strlen($tmpfile))
				unlink($tmpfile);
				
			$this->rawResponse = $result;
	
			$this->response = json_decode($result);
			
			if (is_null($this->response))
			{
				$this->searchResult = false;
				$this->message = $result;
			}
			else
				$this->parse();
			
			return $this->searchResult;
		}

	}

