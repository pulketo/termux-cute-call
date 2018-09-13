<?php
DEFINE("HELP", "
		Command to call a contact searching by name
		it's like this but easier to remember
			termux-contact-list | tr '[:upper:]' '[:lower:]'\
					| jq -c '.[] | select(.name | contains(\"john\"))'\
					| jq -s unique | jq -c '.[] | select(.name | contains(\"doe\"))' ... so on
	Usage:
			pcall john doe -f
				will call the (-f)irst john doe on your list
			pcall john doe 551
				will show john doe with 551 on the name/number, execute termux-telephony-call if there is just one match, or show a list if there is more than one.
			pcall john doe -f -p +521
				will call John Doe but dial a prefix before the number.
			All human output goes to stderr and json output goes to stdout useful if you want to pipe it to jq to prettyfy or extra processing.
");
	require __DIR__ ."/../vendor/autoload.php";
	$opts = new Commando\Command();

// Functions
function fix($i){
	$t = array(
    "\u00c0" =>"À",     "\u00c1" =>"Á",     "\u00c2" =>"Â",     "\u00c3" =>"Ã",     "\u00c4" =>"Ä",     "\u00c5" =>"Å",     "\u00c6" =>"Æ",     "\u00c7" =>"Ç",     "\u00c8" =>"È",     "\u00c9" =>"É",     "\u00ca" =>"Ê",     "\u00cb" =>"Ë",     "\u00cc" =>"Ì",     "\u00cd" =>"Í",     "\u00ce" =>"Î",     "\u00cf" =>"Ï",     "\u00d1" =>"Ñ",     "\u00d2" =>"Ò",     "\u00d3" =>"Ó",     "\u00d4" =>"Ô",     "\u00d5" =>"Õ",     "\u00d6" =>"Ö",     "\u00d8" =>"Ø",     "\u00d9" =>"Ù",     "\u00da" =>"Ú",     "\u00db" =>"Û",     "\u00dc" =>"Ü",     "\u00dd" =>"Ý",     "\u00df" =>"ß",     "\u00e0" =>"à",     "\u00e1" =>"á",     "\u00e2" =>"â",     "\u00e3" =>"ã",     "\u00e4" =>"ä",     "\u00e5" =>"å",     "\u00e6" =>"æ",     "\u00e7" =>"ç",     "\u00e8" =>"è",     "\u00e9" =>"é",     "\u00ea" =>"ê",     "\u00eb" =>"ë",     "\u00ec" =>"ì",     "\u00ed" =>"í",     "\u00ee" =>"î",     "\u00ef" =>"ï",     "\u00f0" =>"ð",     "\u00f1" =>"ñ",     "\u00f2" =>"ò",     "\u00f3" =>"ó",     "\u00f4" =>"ô",     "\u00f5" =>"õ",     "\u00f6" =>"ö",     "\u00f8" =>"ø",     "\u00f9" =>"ù",     "\u00fa" =>"ú",     "\u00fb" =>"û",     "\u00fc" =>"ü",     "\u00fd" =>"ý", "\u00ff" =>"ÿ", "\u202a"=>"", "\u202c"=>"");
	return strtr($i, $t);
	}

function arrSearch($hayStack, $needle){
	$o = array_filter($hayStack, function($el) use ($needle) {
		return ( stripos($el['name'], $needle) !== false || stripos($el['number'], $needle) !== false );
	});
	return $o;
}
function showList($arr){
	$o="";	
	foreach($arr as $k=>$eachArr){
			$o.=print_r($eachArr, true);
	}
	$o = filter($o);
	echo PHP_EOL.$o;
}
function filter($contents){
	$cmd = "echo \"$contents\" | grep -e \"\"";
	$exe = trim(`$cmd`);
	return $exe;
}
function showJson($arr){
	$o = print_r(json_encode($arr, JSON_PRETTY_PRINT),true);
	$o = fix($o);
	echo $o;
}

function show($arr){
	global $opts;
//	if (!$opts['json']){
	if (false){
		showList($arr);
	}else{
		showJson($arr);
	}
}
	$opts->option('h')->aka('help2')->describedAs(HELP)->boolean()->defaultsTo(false);
	$opts->option('f')->aka('callthefirst')->describedAs('Call the first match')->boolean()->defaultsTo(false);
	$opts->option('p')->aka('prefix')->describedAs('A dial prefix (optional)')->defaultsTo("");
	$opts->option('s')->aka('simcall')->describedAs('Simulate call')->boolean()->defaultsTo(false);
	$opts->option('v')->aka('version')->describedAs('Version 1.02')->boolean()->defaultsTo(false);
	$opts->option('c')->aka('call')->describedAs("deprecated, this is the default now.")->boolean()->defaultsTo(false);
	// $opts->option('j')->aka('json')->describedAs('Output in json format')->boolean()->defaultsTo(false);

	$numArgs=0;
	for ($i=0;$i<=15;$i++){
		if ($opts[$i]=="")
			break;
		// echo "$i:".$opts[$i].PHP_EOL;
		$numArgs=$i+1;
	}
	$f = trim(`termux-contact-list| tr '[:upper:]' '[:lower:]'`);
	$arrCL = json_decode($f, true);
	// remove repeats 
	// print_r($arrCL);
	$arrCLU = array_unique($arrCL, SORT_REGULAR);
  // print_r($arrCLU);
	$o = $arrCLU;
	for($i=0;$i<$numArgs;$i++){
		$o = arrSearch($o, $opts[$i]);
	}
	// print_r($o);
	switch(sizeOf($o))
	{
		case "1":
			$extract = array_pop($o);
			$extract["prefix"]=(strlen($opts['prefix'])>0)?$opts['prefix']:"";
			$cmd = "termux-telephony-call ".$opts['prefix'].str_replace(" ","", $extract['number']);
			if ((!$opts['simcall'])){
				if (strlen($opts['prefix'])>0)			
					fwrite(STDERR, "dialing with prefix: ".$opts['prefix'].PHP_EOL);
				$extract["status"]="calling";
				fwrite(STDERR, $cmd.PHP_EOL);
				$exe = trim(`$cmd`);
			}else{
				if ($opts['simcall']){
					fwrite(STDERR, "simulating: $cmd".PHP_EOL);
					$extract["status"]="simcall";
				}
			}
			fwrite(STDERR, PHP_EOL);
			show($extract);
			exit(0);
			break;
		case "0":
			if (($numArgs)>2){
				$u['status']="No matches, try fewer terms";
				fwrite(STDERR, "No matches, try fewer terms".PHP_EOL);
				show($u);
			}else{
				$num = strtr($opts[0], array("+"=>"", "p"=>"", "w"=>"", "#"=>"", "."=>"", ","=>"", "*"=>"", " "=>""));
				if (is_numeric($num)){
					$u['status']="direct-dialing";
					fwrite(STDERR, "Direct dialing...".PHP_EOL);
					$u['number'] = $opts['prefix'].$num;
					$cmd = "termux-telephony-call ".$opts['prefix'].$num;
					if ($opts['simcall'])
						fwrite(STDERR, "simulating: $cmd".PHP_EOL);
					else{
						fwrite(STDERR, "$cmd".PHP_EOL);
						$exe = trim(`$cmd`);
					}	
					show($u);
					
				}else{
				$u['status']="No matches";
				fwrite(STDERR, "No matches".PHP_EOL);
				show($u);
				}
			}
			fwrite(STDERR, PHP_EOL);exit(2);
			break;
		default:
			if ($opts['callthefirst']){
				fwrite(STDERR, "calling first contact on list".PHP_EOL);		
				$u = array_shift($o);
				fwrite(STDERR, "calling... {$u['name']} : {$u['number']}".PHP_EOL);
				$u['status']="calling";
				$u['otherContacts']=$o;
				$cmd = "termux-telephony-call ".$opts['prefix'].str_replace(" ","", $u['number']);
				if ($opts['simcall'])
					fwrite(STDERR, "simulating: $cmd".PHP_EOL);
				else{
					fwrite(STDERR, "$cmd".PHP_EOL);
					$exe = trim(`$cmd`);
				}
				show($u);
				fwrite(STDERR, PHP_EOL);exit(0);
			}else{
				if (!$opts['simcall']){
					$u['status']="Too many matches, ran but without -f"; // shouldn't be called
					$u['otherContacts']=$o;
					show($u);
					fwrite(STDERR, "Too many matches".PHP_EOL);
					fwrite(STDERR, PHP_EOL);exit(1);	
				}else{
					fwrite(STDERR, "Contact list:".PHP_EOL);
					$u['status']="just showing contacts";
					$u['contactList']=$o;
					show($u);
					fwrite(STDERR, PHP_EOL);exit(0);
				}
			}
			fwrite(STDERR, PHP_EOL);exit(3); // shouldn't be called
			break;
	}


