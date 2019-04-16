<?php
if(defined('APP_PATH'))
{
	if($_SERVER['SERVER_ADDR'] == '172.29.64.64')
	{
		if(stristr(strtolower($_SERVER['SCRIPT_FILENAME']),"/csgenio"))						/* means vikastuteja.jdsoftware.com/cs */
		{
			define("GOOGLE_MAPKEY","AIzaSyCdEdEA6yhN9qM9TdSGg9DubVHTC5nHz4M");
		}
		else if(stristr(strtolower($_SERVER['SCRIPT_FILENAME']),"cspin/"))						/* means cspin.jdsoftware.com/cs */
		{
			define("GOOGLE_MAPKEY","ABQIAAAA_ZbPmv0hX_XSyIhPBrq7PBT1sqkg4A337UVKyVQPy-4lQPgRZxSikTg7x_Par0bFpnoBIUWcSbiTvw");
		}
		else if(stristr(strtolower($_SERVER['SCRIPT_FILENAME']),"idccspin/"))						/* means vikastuteja.jdsoftware.com/idccspin */
		{
			define("GOOGLE_MAPKEY","ABQIAAAA_ZbPmv0hX_XSyIhPBrq7PBRbGzk3QrUtz4vFfVhtbN8NpfYh9xRN-G-4FhMKT49Rcc0zPRs7I9XFXg");
		}
		else if(stristr(strtolower($_SERVER['HTTP_HOST']),"csgenio.jdsoftware.com/"))			/* means csgenio.jdsoftware.com/idccspin */
		{
			define("GOOGLE_MAPKEY","AIzaSyCdEdEA6yhN9qM9TdSGg9DubVHTC5nHz4M");
		}
		
	}
	else if( in_array($_SERVER['SERVER_ADDR'], array('192.168.17.217', '192.168.17.227', '192.168.1.86'))) /* all possible servers for remote cities modules */
	{
       define("GOOGLE_MAPKEY","ABQIAAAAZHNtsBIjEMe4kHPFfrAW5hT9tNTCUuQc4G36j4PCcQFnEs8R3BSEz7Ya4NEzdkKxGO3_7QqrIeY2kA");
	}
	else
	{
        $ip_mid_add = array('0', '8', '16', '26', '32', '40', '50', '56');
        $valid_server = false;
        foreach($ip_mid_add as $each_ip)
        {
            if($_SERVER['SERVER_ADDR']=='172.29.' . $each_ip . '.217' || $_SERVER['SERVER_ADDR']=='172.29.' . $each_ip . '.227' || $_SERVER['SERVER_ADDR']=='172.29.' . $each_ip . '.237')
            {
                $valid_server = true;
				if($_SERVER['SERVER_ADDR'] == '172.29.' . $each_ip . '.217')	/* for CS */
				{
					switch ($each_ip)
					{
						case '0':
						define("GOOGLE_MAPKEY","ABQIAAAAZHNtsBIjEMe4kHPFfrAW5hTNZ-sgRREr-oWfYuJJBP1F29PJwRTe-DN2s22UHhiQnLd-3DTSI0GsRQ");
						break;
						case '8':
						define("GOOGLE_MAPKEY","ABQIAAAAZHNtsBIjEMe4kHPFfrAW5hSiUHAqmAglf4IcUqeWUSpfCzHvLBQdSiHgfzFBYDdsXVDcVxwyG5x2Yw");
						break;
						case '16':
						define("GOOGLE_MAPKEY","ABQIAAAAZHNtsBIjEMe4kHPFfrAW5hQN3g3i7ysaTds_e9LqI-3xmgNgCRST6-rmCvz2bsr3LBQhWXWMzsNcQQ");
						break;
						case '26':
						define("GOOGLE_MAPKEY","ABQIAAAAZHNtsBIjEMe4kHPFfrAW5hQsDpy1nkJjAV8FnMShvIBXER0sVRSywyUHrmXdBd7KnnScRVVHeCQA5w");
						break;
						case '32':
						define("GOOGLE_MAPKEY","ABQIAAAAZHNtsBIjEMe4kHPFfrAW5hSI8pO9VCAxTxn3NBvrBeHWyyAenBQr9vJsNTTXngOkPDwscrVpQRVA0g");
						break;
						case '40':
						define("GOOGLE_MAPKEY","ABQIAAAAZHNtsBIjEMe4kHPFfrAW5hTc2IEPfJXz9pXKMT1RrAq3lr56iBRpBXllCYCpenc_-poRpysIuEs19Q");
						break;
						case '50':
						define("GOOGLE_MAPKEY","ABQIAAAAZHNtsBIjEMe4kHPFfrAW5hQFZ0L-kn8ypmpJZ68PD1UX8vtiexTf3mHGrT-JBGnmMkHFBqVHIdXF8w");
						break;
						case '56':
						define("GOOGLE_MAPKEY","ABQIAAAAZHNtsBIjEMe4kHPFfrAW5hSxbTgMqEL2nC5xZRw5nTL7UDgmBBRLMDapP5NDqHXizEo-q2paEUzBJQ");
						break;
					}
				}
				else if($_SERVER['SERVER_ADDR'] == '172.29.' . $each_ip . '.237')  /* for TME */
				{
					switch ($each_ip)
					{
						case '0':
						define("GOOGLE_MAPKEY","ABQIAAAA_ZbPmv0hX_XSyIhPBrq7PBSq_t3iXmlnuy6xi3iH6WQgr5CRmxS8QZQVpV6JYlUqYiqfGIVfjAN43A");
						break;
						case '8':
						define("GOOGLE_MAPKEY","ABQIAAAA_ZbPmv0hX_XSyIhPBrq7PBTgXfSS49RQWQJNUYXa7WmTdJ9UABT50CJ06YJWy8iPBgTtBnmQBlHuNQ");
						break;
						case '16':
						define("GOOGLE_MAPKEY","ABQIAAAA_ZbPmv0hX_XSyIhPBrq7PBQ-u7WsxG0QUdcOKvRnPAVFpVhINRST8eO2y-Lnjvv4GW1Ff8_ZFxBx0A");
						break;
						case '26':
						define("GOOGLE_MAPKEY","ABQIAAAA_ZbPmv0hX_XSyIhPBrq7PBTDWAHZU-M5E7XO-b7TC3cda4f1pBRyD5d4O2hrlkMC4QZ5cTJa49i2tA");
						break;
						case '32':
						define("GOOGLE_MAPKEY","ABQIAAAA_ZbPmv0hX_XSyIhPBrq7PBT7KSw8T0LiDvClZX0Y0zdeDh350hQkRBxM_HbFcxW2hZo5fiudi6Uo1w");
						break;
						case '40':
						define("GOOGLE_MAPKEY","ABQIAAAA_ZbPmv0hX_XSyIhPBrq7PBQZjROIl_tfctVopfcZwFmdvk_wjBQ1xrkjZ6GnHO_70kc3bO4hKyrhqg");
						break;
						case '50':
						define("GOOGLE_MAPKEY","ABQIAAAA_ZbPmv0hX_XSyIhPBrq7PBQOaqlKcnSxpsOBlaj5XqZL7MKGFhTHu5-EHWb3ryOyspB9eBAYLM24_Q");
						break;
						case '56':
						define("GOOGLE_MAPKEY","ABQIAAAA_ZbPmv0hX_XSyIhPBrq7PBSpLtU9PPOH1FhGAMZEFFG8mPKKxhQ8o4p8frXmBGh3qxOdrfi9WTQn1A");
						break;
					}
				}
            }
        }
	}
}

?>
