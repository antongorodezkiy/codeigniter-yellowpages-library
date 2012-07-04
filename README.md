codeigniter-yellowpages-library
===============================

Codeigniter library for working with Yellow Pages API


Example in controller:

    $this->load->library('YP');
  	
		$searchResult = $this->yp->searchAll($business,$location);

		if ($searchResult && $this->yp->count)
		{
			foreach($this->yp->listings as $result)
			{
        print_r($result);
      
				if (!$result->latitude or !$result->longitude)
					continue;
				
				if ($this->yp->total > 100)
				  $res = $this->someOperation($result->latitude,$result->longitude);
				
			}
    }