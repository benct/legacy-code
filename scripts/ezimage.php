<?php
/*******************************************************************************
* ezimage.php                                                                  *
********************************************************************************
* Simple image manipulating script (supports jpg, gif and png)                 *
* ============================================================================ *
* Version:                    1.0                                              *
* Software by:                Ben Tomlin                                       *
* Support, News, Updates at:  http://tomlin.no                                 *
********************************************************************************
* This program is free software; you can redistribute it and/or modify it      *
* under the terms of the GNU General Public License as published by the        *
* Free Software Foundation (version 2 or later).                               *
*                                                                              *
* This program is distributed in the hope that it is and will be useful, but   *
* WITHOUT ANY WARRANTIES; without even any implied warranty of MERCHANTABILITY *
* or FITNESS FOR A PARTICULAR PURPOSE.                                         *
********************************************************************************
*  $ezimg = new EzImage();                                  -----------        *
*  $ezimg->load('filename.png');                            | Example |        *
*  $ezimg->resizeToWidth(250);                              |  Usage  |        *
*  $ezimg->save('newfile.png', IMAGETYPE_PNG);              -----------        *
*******************************************************************************/

class EzImage
{
    var $image;
    var $imagetype;

    /**
     * Load a specified file from the given filename/path
     * @param filename  the path and name of the file to load
     */
    function load($filename)
    {
        $imageinfo = getimagesize($filename);
        $this->imagetype = $imageinfo[2];
        if ($this->imagetype == IMAGETYPE_JPEG) {
            $this->image = imagecreatefromjpeg($filename);
        } else if ($this->imagetype == IMAGETYPE_GIF) {
            $this->image = imagecreatefromgif($filename);
        } else if ($this->imagetype == IMAGETYPE_PNG) {
            $this->image = imagecreatefrompng($filename);
        }
    }
    
    /**
     * Save the current image as a specified type
     * @param filename    (new) name of the file to save
     * @param imagetype   image file type (IMAGETYPE_JPEG | IMAGETYPE_GIF | IMAGETYPE_PNG)
     * @param compression rate of compression for jpeg images
     * @param permissions permissions to set on the newly created file
     */
    function save($filename, $imagetype = IMAGETYPE_JPEG, $compression = 75, $permissions = null)
    {
        if ($imagetype == IMAGETYPE_JPEG) {
            imagejpeg($this->image, $filename, $compression);
        } else if ($imagetype == IMAGETYPE_GIF) {
            imagegif($this->image, $filename);
        } else if ($imagetype == IMAGETYPE_PNG) {
            imagepng($this->image, $filename);
        }
        if ($permissions != null) {
            chmod($filename, $permissions);
        }
    }
    
    /**
     * Output the image directly
     * @param imagetype  image file type (IMAGETYPE_JPEG | IMAGETYPE_GIF | IMAGETYPE_PNG)
     */
    function output($imagetype = IMAGETYPE_JPEG)
    {
        if ($imagetype == IMAGETYPE_JPEG) {
            imagejpeg($this->image);
        } else if ($imagetype == IMAGETYPE_GIF) {
            imagegif($this->image);
        } else if ($imagetype == IMAGETYPE_PNG) {
            imagepng($this->image);
        }
    }
    
    /**
     * Get the width of the current image
     * @return int  the image width
     */
    function getWidth()
    {
        return imagesx($this->image);
    }
    
    /**
     * Get the height of the current image
     * @return int  the image height
     */
    function getHeight()
    {
        return imagesy($this->image);
    }
    
    /**
     * Resize the image to a specified width (while maintaining the ratio)
     * @param width  new width of the image
     */
    function resizeToWidth($width)
    {
        $ratio = $width / $this->getWidth();
        $height = $this->getheight() * $ratio;
        $this->resize($width, $height);
    }
    
    /**
     * Resize the image to a specified height (while maintaining the ratio)
     * @param height  new height of the image
     */
    function resizeToHeight($height)
    {
        $ratio = $height / $this->getHeight();
        $width = $this->getWidth() * $ratio;
        $this->resize($width, $height);
    }

    /**
     * Resize (scale) the image by percent (while maintaining the ratio)
     * @param scale  percent to scale new image (1-100)
     */
    function scale($scale)
    {
        $width = $this->getWidth() * $scale/100;
        $height = $this->getheight() * $scale/100;
        $this->resize($width, $height);
    }

    /**
     * Does the actual resizing of the current image
     * @param width  new width of the image
     * @param height new height of the image
     */
    function resize($width, $height)
    {
        $newimage = imagecreatetruecolor($width, $height);
        imagecopyresampled($newimage, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
        $this->image = $newimage;
    }
}
