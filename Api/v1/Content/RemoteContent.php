<?php

namespace Api\v1\Content;
use Api\v1\Services\MimeTypeService;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;


/**
 * Class RemoteContent
 * @package Api\v1\Content
 */
class RemoteContent implements ContentInterface
{
    /**
     * @var string
     */
    private $fileUrl;


    public function __construct($fileUrl)
    {
        $this->fileUrl = $fileUrl;
    }

    /**
     * @inheritdoc
     */
    public function get()
    {
        $ch = curl_init($this->fileUrl);

        curl_setopt($ch, CURLOPT_FAILONERROR, true);

        $result = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($status >= 400) {
            return false;
        } else {
            return $result;
        }
    }

    /**
     * @inheritdoc
     */
    public function out($handle)
    {
        $ch = curl_init($this->fileUrl);

        curl_setopt($ch, CURLOPT_FILE, $handle);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);

        $result = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $size = curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD);

        curl_close($ch);

        if ($result === false || $status >= 400) {
            return 0;
        }

        return $size;
    }


    /**
     * @return string
     */
    public function getExtension()
    {
        if ($ext = pathinfo($this->fileUrl, PATHINFO_EXTENSION)) {
            return $ext;
        }

        $headers = get_headers($this->fileUrl, 1);
        if ($headers && isset($headers["Content-Type"]))
            return MimeTypeService::getExtensionFromMimetype($headers["Content-Type"]);

        return null;
    }
}