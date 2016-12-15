<?php

namespace Api\v1\Content;


/**
 * Class InlineContent
 * @package Api\v1\Content
 */
class InlineContent implements ContentInterface
{
    /**
     * @var string
     */
    private $content;

    /**
     * @var string
     */
    private $extension;

    /**
     * InlineContent constructor.
     * @param string $content
     * @param string $extension
     */
    public function __construct($content, $extension = "txt")
    {
        $this->content = $content;
        $this->extension = $extension;
    }

    /**
     * @inheritdoc
     */
    public function get()
    {
        return $this->content;
    }

    /**
     * @inheritdoc
     */
    public function out($handle)
    {
        return fwrite($handle, $this->content);
    }

    /**
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }
}