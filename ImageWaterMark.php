<?php
/**
 * Created by PhpStorm.
 * User: luisclaudio
 * Date: 10/10/2018
 * Time: 10:19
 */

class ImageWaterMark{
    private $imageUrl;

    /**
     * @return mixed
     */
    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    /**
     * @param mixed $imageUrl
     */
    public function setImageUrl($imageUrl)
    {
        $this->imageUrl = $imageUrl;
    }

    /**
     * @return mixed
     */
    public function getPosX()
    {
        return $this->posX;
    }

    /**
     * @param mixed $posX
     */
    public function setPosX($posX)
    {
        $this->posX = $posX;
    }

    /**
     * @return mixed
     */
    public function getPosY()
    {
        return $this->posY;
    }

    /**
     * @param mixed $posY
     */
    public function setPosY($posY)
    {
        $this->posY = $posY;
    }
    private $posX;
    private $posY;

    public function __construct($imageUrl, $posX, $posY)
    {

        $this->imageUrl = $imageUrl;
        $this->posX = $posX;
        $this->posY = $posY;

    }
}