<?php

use API\Application;

require APPLICATION_ROOT . '/library/strutils.php';


class Sorter
{
    /**
     * Minimal similarity constants
     */
    const MIN_DESC_SIMILARITY = 40;
    const MIN_TITLE_SIMILARITY = 95;
    const MIN_DEV_SIMILARITY = 95;

    protected static $errors = array();

    /**
     * Returns last errors occurs during application sorting
     *
     * @return array Errors list
     */
    public static function getLastErrors()
    {
        return self::$errors;
    }

    protected static function cleanErrors()
    {
        self::$errors = array();
    }

    /**
     * Returns all apps data grouped by store.
     *
     * @param $list array URLs list
     * @return array
     * @throws Exception
     */
    protected static function getAppsData($list)
    {
        $result = array();
        foreach ($list as $url)
        {
            try {
                $app = Application::factory($url);
            }
            catch (Exception $e) {
                self::$errors[] = array(
                    'message' => $e->getMessage(),
                    'url' => $url
                );
                continue;
            }

            $result[$app->getSource()][] = array(
                'url' => $url,
                'title' => $app->getTitle(),
                'developer' => $app->getDeveloper(),
                'description' => $app->getDescription(),
            );
        }

        return $result;
    }

    /**
     * Group URLs by application name
     *
     * @param $list array List of URLs
     * @return array Sorted URLs list
     * @throws Exception
     */
    public static function groupApps($list)
    {
        // Clear previous errors list
        self::cleanErrors();

        $result = array();

        // Retrieve and store applications data
        $storesItems = self::getAppsData($list);

        $index = 0;
        $stores = array_keys($storesItems);
        foreach ($stores as $store)
        {
            $index++;

            foreach ($storesItems[$store] as &$item)
            {
                // Skip items which are already sorted to some group
                if (!empty($item['sorted'])) {
                    continue;
                }

                // Create new URL group (by application name)
                $result[$item['title']] = array(
                    'commonTitle' => $item['title'],
                    'urls' => array($item['url']),
                );

                // Get neighbour stores from the right
                $neighbours = array_slice($stores, $index, count($stores) - $index);
                foreach ($neighbours as $nextStore)
                {
                    foreach ($storesItems[$nextStore] as &$nextItem)
                    {
                        similar_text(strtolower($item['description']), strtolower($nextItem['description']), $descSimilarity);
                        similar_text(strtolower($item['title']), strtolower($nextItem['title']), $titleSimilarity);
                        similar_text(strtolower($item['developer']), strtolower($nextItem['developer']), $devSimilarity);

                        if ($descSimilarity < self::MIN_DESC_SIMILARITY)
                        {
                            if (($titleSimilarity < self::MIN_TITLE_SIMILARITY) &&
                                ($devSimilarity < self::MIN_DEV_SIMILARITY))
                            {
                                continue;
                            }
                        }

                        $nextItem['sorted'] = true;

                        $result[$item['title']]['commonTitle'] = longest_common_substring($item['title'], $nextItem['title']);
                        $result[$item['title']]['urls'][] = $nextItem['url'];
                    }
                }
            }
        }

        return $result;
    }
}
