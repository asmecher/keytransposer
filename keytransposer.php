<?php

/**
 * Key Transposer
 * Copyright (c) 2013 by Alec Smecher
 *
 * Find key transpositions (when you try to type things with your hands on the
 * wrong home position) that actually turn out to be real words. For example:
 * set your fingers one column to the left of home, and "bored" becomes
 * "views".
 *
 * Run this without any options to find all one-direction transpositions (up,
 * down, left, right). Alternately, specify one or more transpositions
 * directions you want to include. For example:
 *
 * php keytransposer.php rr dl
 *
 * This will list all transpositions two spots right, and to the southwest.
 */

define('DICTIONARY_PATH', '/usr/share/dict/american-english');

// My dictionary contains a bunch of extra letters that aren't words; omit them.
$omissions = array('b', 'c', 'd', 'e', 'f', 'g', 'h', 'j', 'k', 'l', 'm', 'n', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'z', 'y', 'z');

$keys = array(
	'q' => array(null, 'w', 'a', null),
	'w' => array(null, 'e', 's', 'q'),
	'e' => array(null, 'r', 'd', 'w'),
	'r' => array(null, 't', 'f', 'e'),
	't' => array(null, 'y', 'g', 'r'),
	'y' => array(null, 'u', 'h', 't'),
	'u' => array(null, 'i', 'j', 'y'),
	'i' => array(null, 'o', 'k', 'u'),
	'o' => array(null, 'p', 'l', 'i'),
	'p' => array(null, null, null, 'o'),
	'a' => array('q', 's', 'z', null),
	's' => array('w', 'd', 'x', 'a'),
	'd' => array('e', 'f', 'c', 's'),
	'f' => array('r', 'g', 'v', 'd'),
	'g' => array('t', 'h', 'b', 'f'),
	'h' => array('y', 'j', 'n', 'g'),
	'j' => array('u', 'k', 'm', 'h'),
	'k' => array('i', 'l', null, 'j'),
	'l' => array('o', null, null, 'k'),
	'z' => array('a', 'x', null, null),
	'x' => array('s', 'c', null, 'z'),
	'c' => array('d', 'v', null, 'x'),
	'v' => array('f', 'b', null, 'c'),
	'b' => array('g', 'n', null, 'v'),
	'n' => array('h', 'm', null, 'b'),
	'm' => array('j', null, null, 'n'),
);

// Load the dictionary
$words = array_diff(array_map('trim', file(DICTIONARY_PATH)), $omissions);

// Invert the words array for quicker testing
$wordsInverted = array_flip($words);

$allDirections = array('u', 'r', 'd', 'l');
$directionMap = array('u' => 0, 'r' => 1, 'd' => 2, 'l' => 3);

// Figure out how the user is invoking the command; default to all
// single transpositions if nothing is specified.
array_shift($argv); // Pop the script name off the arguments list
$directions = $argv; // What's left is the list of directions.
if (empty($directions)) $directions = $allDirections; // default
else foreach($directions as $directionString) { // Check options
	for ($i=0; $i<strlen($directionString); $i++) {
		$direction = $directionString[$i];
		if (!isset($directionMap[$direction])) {
			die('Invalid direction specified.');
		}
	}
}

// Track the words we've already output
$possibilities = array();

// Loop through all dictionary words and try them out.
foreach ($words as $word) {
	if (empty($word)) continue; // In case there are blanks

	foreach ($directions as $directionString) { // For all transpositions specified...
		$potential = '';
		for ($i=0; $i<strlen($word); $i++) { // For each letter in the word we're testing...
			// Make sure the letter exists
			if (!isset($keys[$word[$i]])) break;

			// Loop through all directions specified and apply them one at a time
			for ($j=0, $letter = $word[$i]; $j<strlen($directionString); $j++) {
				$direction = $directionString[$j];
				// Build up the potental word with it
				$letter = $keys[$letter][$directionMap[$direction]];
				if (!$letter) break;
			}
			// If a letter exists in that direction, keep it.
			if ($letter) $potential .= $letter;
		}
		// If all letters in the word could be transposed, and the resulting word exists,
		if (strlen($potential) == strlen($word) && isset($wordsInverted[$potential])) {
			// and we haven't already transposed this word in the opposite direction,
			if (!isset($possibilities[$potential]) || !in_array($word, $possibilities[$potential])) {
				// display it
				echo "$word / $potential\n";

				// and record it to avoid duplicates.
				$possibilities[$word][] = $potential;
			}
		}
	}
}

?>
