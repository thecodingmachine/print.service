<?php

namespace Api\v1\Content;


/**
 * Interface ContentInterface
 * @package Api\v1\Content
 */
interface ContentInterface
{
    /**
     * return content as value
     *
     * @return string
     */
    public function get();

    /**
     * write content in resource
     *
     * @param  resource $handle
     * @return int
     */
    public function out($handle);

    /**
     * @return string
     */
    public function getExtension();
}