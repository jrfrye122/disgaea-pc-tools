<?php


	if (!isset($argv[1])) {
		$filename	= "data/SUBDATA.DAT/START.DAT/SCRIPT.DAT";
		if (!file_exists($filename)) {
			die("File $filename missing. Add it or give SCRIPT.DAT location as an argument\n");
		}

	} elseif (!file_exists($argv[1])) {
		die("File not found: $argv[1]\n");

	} else {

		$filename	= $argv[1];
	}


	// This technically used to be 00000000.bin, but the new unpacker has a filename list it uses
	$file	= file_get_contents($filename);
	$len	= strlen($file);

	chdir("../");
	include "utils.php";

	/*
	Header
	u32 count;
	u32 offsets[count];
	u32 ids[count];

	where e.g. offsets[0] is the offset to the first script entry in the file (counted from after the ids array ends) and ids[0] is the script ID for the same script


	<FireFly> *most* of the commands are  u8 op; u8 nargs; u8 args[nargs];
	<FireFly> which is very generous that they give the size of the command upfront
	<FireFly> Now of course the ones that *don't* follow that pattern are variable-length and really annoying to try to find a pattern among


	*/


	$count		= \Disgaea\DataStruct::getLEValue(substr($file, 0x00000000, 0x00000004));
	$offsetofs	= 0x00000004;						// First after "count"
	$idsofs		= 0x00000004 * ($count + 1);		// After offset array
	$dataofs	= 0x00000004 * ($count * 2 + 1);	// After ids array

	$ids		= array();
	$offsets	= array();
	$idmap		= array();

	for ($i = 0; $i < $count; $i++) {
		$offsets[$i]		= \Disgaea\DataStruct::getLEValue(substr($file, $offsetofs + 4 * $i, 0x00000004));
		$ids[$i]			= \Disgaea\DataStruct::getLEValue(substr($file, $idsofs + 4 * $i, 0x00000004));

		if (isset($idmap[$ids[$i]])) {
			print("Duplicate script ID encountered: ". $idmap[$ids[$i]] ."\n");
		}

		$idmap[$ids[$i]]	= $i;
	}

	$data		= substr($file, $dataofs);

	for ($i = 0; $i < $count; $i++) {
		printf("%04x: ofs %08x   id %8d   ", $i, $offsets[$i], $ids[$i]);

		$len		= isset($offsets[$i + 1]) ? ($offsets[$i + 1] - $offsets[$i]) : strlen($data);

		$scriptdata	= substr($data, $offsets[$i], $len);

		file_put_contents("stuff/scripts/". sprintf("%08d", $ids[$i]) .".bin", $scriptdata);

		print "\n";


	}

	print "\n";



