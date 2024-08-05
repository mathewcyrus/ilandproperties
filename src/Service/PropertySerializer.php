<?php

namespace App\Service;

use App\Entity\Property;

class PropertySerializer
{
    public function serialize(Property $property): array
    {
        $images = [];
        foreach ($property->getImages() as $image) {
            $images[] = [
                'image_id' => $image->getImageId(),
                'image_path' => $image->getImagePath(),
            ];
        }
        return [
            'property_id' => $property->getPropertyId()->toString(), 
            'property_type' => $property->getPropertyType(),
            'property_description' => $property->getPropertyDescription(),
            'property_price' => $property->getPropertyPrice(),
            'property_location' => $property->getPropertyLocation(),
            'property_age' => $property->getPropertyAge(),
            'date_posted' => $property->getDatePosted()->format('Y-m-d H:i:s'),
            'property_owner' => $property->getPropertyOwner() ? $property->getPropertyOwner()->getUserId() : null,
            'images' => $images,
        ];
    }
}
