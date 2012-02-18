<?php
/*
 * SMF PasteBin
 * Author: SleePy (JeremyD)
 * Repository: https://github.com/jdarwood007/pastebin
 * License: BSD 3 Clause; See license.txt
*/
if (!defined('SMFPasteBin')) { exit('[' . basename(__FILE__) . '] Direct access restricted');}

/*
* Basic Anti-Spam handler for Pastebin.
*/
class pAS_recaptcha extends pAS
{
	/*
	* Test whether this anti-spam method is enabled or not.
	*/
	public function classActive()
	{
		if (pBS::get('recaptcha_key') === null || pBS::get('recaptcha_private_key') === null)
			return false;
		return true;
	}

	/*
	* Verify the that we correctly entered the anti-spam stuff.
	*/
	public function verify($warnings)
	{
		$data = implode('&', array(
			'privatekey' => pBS::get('recaptcha_private_key'),
			'remoteip' => $_SERVER['REMOTE_ADDR'],
			'challenge' => $this->cleanInput($_POST['recaptcha_challenge_field']),
			'response' => $this->cleanInput($_POST['recaptcha_response_field'])
		));


		// Connect to the collection script.
		$response = '';
		$fp = @fsockopen('www.google.com', 80, $errno, $errstr);
		if ($fp)
		{
			$out = 'POST /recaptcha/api/verify HTTP/1.1' . "\r\n";
			$out .= 'Host: www.google.com' . "\r\n";
			$out .= "User-Agent: reCAPTCHA/PHP\r\n";
			$out .= 'Content-Type: application/x-www-form-urlencoded' . "\r\n";
			$out .= 'Content-Length: ' . strlen($data) . "\r\n\r\n";
			$out .= $data . "\r\n";
			$out .= 'Connection: Close' . "\r\n\r\n";
			fwrite($fp, $out);

			while (!feof($fs))
				$response .= fgets($fs, 1160);
			fclose($fp);

			$response = explode("\r\n\r\n", $response, 2);

			if (trim($response[0]) == 'true')
				return true;
			else
			{
				$warnings[] = $response[1];
				return false;
			}
		}
		else
		{
			$warnings[] = 'Could not connect to the remote ReCaptcha service, verification failed';
			return false;
		}
	}

	/*
	* For the template.
	*/
	public function template()
	{
		echo '
		<script type="text/javascript" src="http://www.google.com/recaptcha/api/challenge?k=', pBS::get('recaptcha_key'), '"></script>
		<noscript>
			<iframe src="http://www.google.com/recaptcha/api/noscript?k=', pBS::get('recaptcha_key') , '" height="300" width="500" frameborder="0"></iframe><br>
			<textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
			<input type="hidden" name="recaptcha_response_field" value="manual_challenge" />
		</noscript>';
	}

	private function cleanInput($var)
	{
		return urlencode(stripslashes($var));
	}
}