<?php

if (!defined( 'URBER_PRODUCT_FEED_PLUGIN_DIR' )) {
    exit;
}

class UPF_Cache
{
    /**
     * @var UPF_Core
     */
    protected $core;

    /**
     * UPF_Cache constructor.
     * @param UPF_Core $core
     */
    public function __construct(UPF_Core $core)
    {
        $this->core = $core;
    }

    /**
     * Get feed from cache file
     *
     * @param $filePath
     * @return bool|string
     */
    public function getFeedFromCache($filePath)
    {
        if ($filePath) {
            return file_get_contents($filePath);
        }

        return false;
    }

    /**
     * Save feed to file
     *
     * @param $feed
     */
    public function saveFeedToCache($feed)
    {
        //check and create dir
        if (!is_dir(URBER_PRODUCT_FEED_CACHE_DIR)) {
            mkdir(URBER_PRODUCT_FEED_CACHE_DIR);
        }

        $dateTimePrefix = date('Y-m-d-H-i-s') . '_';
        $cacheFile = URBER_PRODUCT_FEED_CACHE_DIR . '/' . $dateTimePrefix . URBER_PRODUCT_FEED_CACHE_NAME_SUFFIX;

        file_put_contents($cacheFile, $feed);
    }

    /**
     * Check feed cache expited
     *
     * @param $cacheFile
     * @return bool
     */
    public function checkFeedCacheExpired($cacheFile)
    {
        if (!$cacheFile) {
            return true;
        }

        //get cache duration value from config
        $duration = get_option(URBER_PRODUCTFEED_CONFIG)['cache'];

        if (empty($duration)) {
            return true;
        }

        /** @var DateTime $cacheTimeStamp */
        $cacheTimeStamp = GetCacheFileDate($cacheFile)->getTimestamp();

        /** @var DateTime $nowTimeStamp */
        $nowTimeStamp = date_create()->getTimestamp();

        $timeDiff = $nowTimeStamp - $cacheTimeStamp;
        $durationInSeconds = $duration * 60 * 60;

        if ($timeDiff >= $durationInSeconds) {
            return true;
        }

        return false;
    }

    /**
     * Get last cache file
     *
     * @return bool|string
     */
    public function getLastCacheFile()
    {
        $files = scandir(URBER_PRODUCT_FEED_CACHE_DIR);

        foreach ($files as $file) {
            $filePath = URBER_PRODUCT_FEED_CACHE_DIR . '/' . $file;
            $match = preg_match('/[0-9]{4}(-[0-9]{2}){5}_product-feed/', $file);

            if ($match === 1 && is_file($filePath)) {
                return $filePath;
            }
        }

        return false;
    }

    /**
     * Get datetime cache file
     *
     * @param $cacheFile
     * @return bool|DateTime
     */
    public function getCacheFileDate($cacheFile)
    {
        $matchResult = preg_match('/([0-9]{4}(-[0-9]{2}){5})_product-feed/', $cacheFile, $matches);

        if ($matchResult === 1) {
            $dateTime = date_create_from_format('Y-m-d-H-i-s', $matches[1]);
            return $dateTime;
        }

        return false;
    }

    /**
     * Delete all cache files
     */
    public function flushAllCacheFiles()
    {
        $files = scandir(URBER_PRODUCT_FEED_CACHE_DIR);

        foreach ($files as $file) {
            $filePath = URBER_PRODUCT_FEED_CACHE_DIR . '/' . $file;

            if (is_file($filePath)){
                unlink($filePath);
            }
        }
    }
}