<?php

namespace App\Service;

use App\Entity\Image;

class ImageSerializer
{
    public function serialize(Image $image): array
    {
        return [
            'image_id' => $image->getImageId(),
            'image_path' => $image->getImagePath(),
            
        ];
    }
}
