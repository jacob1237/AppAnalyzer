<?php

/**
 * Longest common substring algorithm from
 * http://en.wikibooks.org/wiki/Algorithm_Implementation/Strings/Longest_common_substring#PHP
 *
 * @param $str1 string
 * @param $str2 string
 * @return string string
 */
function longest_common_substring($str1, $str2)
{
    $str1_len = strlen($str1);
    $str2_len = strlen($str2);
    $return = '';

    if (empty($str1_len) || empty($str2_len)) {
        return $return;
    }

    // Initialize the CSL array to assume there are no similarities
    $sequence = array_fill(0, $str1_len, array_fill(0, $str2_len, 0));

    $largest_size = 0;

    for ($i = 0; $i < $str1_len; $i++)
    {
        for ($j = 0; $j < $str2_len; $j++)
        {
            // Check every combination of characters
            if ($str1[$i] === $str2[$j])
            {
                // These are the same in both strings
                if ($i === 0 || $j === 0) {
                    $sequence[$i][$j] = 1;
                } else {
                    $sequence[$i][$j] = $sequence[$i - 1][$j - 1] + 1;
                }

                if ($sequence[$i][$j] > $largest_size) {
                    $largest_size = $sequence[$i][$j];
                    $return = '';
                }

                if ($sequence[$i][$j] === $largest_size) {
                    $return = substr($str1, $i - $largest_size + 1, $largest_size);
                }
            }
        }
    }

    // Return the list of matches
    return trim($return);
}
