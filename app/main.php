<?php

use API\Application;

require APPLICATION_ROOT . '/library/strutils.php';


class SimilarDetector
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

            $result[] = $app;
        }

        return $result;
    }

    /**
     * Check applications similarity
     *
     * @param $app1 Application\ApplicationInterface
     * @param $app2 Application\ApplicationInterface
     */
    protected static function isSimilar($app1, $app2)
    {
        similar_text(
            strtolower($app1->getDescription()),
            strtolower($app2->getDescription()),
            $desc
        );

        similar_text(
            strtolower($app1->getTitle()),
            strtolower($app2->getTitle()),
            $title
        );

        similar_text(
            strtolower($app1->getDeveloper()),
            strtolower($app2->getDeveloper()),
            $developer
        );

        if ($desc < self::MIN_DESC_SIMILARITY)
        {
            if (($title < self::MIN_TITLE_SIMILARITY) && ($developer < self::MIN_DEV_SIMILARITY)) {
                return false;
            }
        }

        return true;
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

        $items = self::getAppsData($list);
        $count = count($items);

        $result = array();

        for ($i = 0; $i < $count; $i++)
        {
            if (!empty($items[$i]->sorted)) {
                continue;
            }

            $title1 = $items[$i]->getTitle();

            // Create new URL group (by application name)
            $result[$title1] = array(
                'commonTitle' => $title1,
                'urls' => array($items[$i]->getUrl()),
            );

            for ($j = 0; $j < $count; $j++)
            {
                if (($i == $j) || !self::isSimilar($items[$i], $items[$j])) {
                    continue;
                }

                $items[$j]->sorted = true;
                $title2 = $items[$j]->getTitle();

                $result[$title1]['commonTitle'] = longest_common_substring($title1, $title2);
                $result[$title1]['urls'][] = $items[$j]->getUrl();
            }
        }

        return $result;
    }
}
