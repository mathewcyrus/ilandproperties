<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Property;
use App\Entity\User;
use App\Service\PropertySerializer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class PropertyController extends AbstractController
{
    private $entityManager;
    private $propertySerializer;

    public function __construct(EntityManagerInterface $entityManager, PropertySerializer $propertySerializer)
    {
        $this->entityManager = $entityManager;
        $this->propertySerializer = $propertySerializer;
    }

    #[Route('/newproperty', name: 'app_property', methods: ['POST'])]
    public function createNewProperty(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $property = new Property();
        $property->setPropertyType($data['property_type']);
        $property->setPropertyDescription($data['property_description']);
        $property->setPropertyPrice($data['property_price']);
        $property->setPropertyLocation($data['property_location']);
        $property->setPropertyAge($data['property_age']);
        $property->setDatePosted(new \DateTime($data['date_posted']));

        $user = $this->entityManager->getRepository(User::class)->find($data['property_owner']);
        if ($user) {
            $property->setPropertyOwner($user);
        } else {
            return $this->json(['error' => 'User not found'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Handle images
        if (isset($data['images']) && is_array($data['images'])) {
            foreach ($data['images'] as $imageData) {
                $image = new Image();
                $image->setImagePath($imageData['image_path'] ?? null);
                $image->setProperty($property); // Link image to the property

                $this->entityManager->persist($image);
            }
        }

        $this->entityManager->persist($property);
        $this->entityManager->flush();

        return new JsonResponse($this->propertySerializer->serialize($property), JsonResponse::HTTP_CREATED);
    }

    //Get a single property
    #[Route('/property/{id}', name: 'get_property', methods: ['GET'])]
    public function getProperty(string $id): JsonResponse
    {
        $property = $this->entityManager->getRepository(Property::class)->find($id);

        if (!$property) {
            return $this->json(['error' => 'Property not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        return new JsonResponse($this->propertySerializer->serialize($property));
    }

    //get properties fora certain user
    #[Route('/user/{userId}/properties', name: 'get_properties_by_user', methods: ['GET'])]
    public function getPropertiesByUser(string $userId): JsonResponse
    {
        // Fetch the user by ID
        $user = $this->entityManager->getRepository(User::class)->find($userId);

        if (!$user) {
            return $this->json(['error' => 'User not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Fetch properties owned by the user using the custom repository method
        $properties = $this->entityManager->getRepository(Property::class)->findByUser($user);

        if (empty($properties)) {
            return $this->json(['message' => 'No properties found for this user'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Serialize and return the properties
        return new JsonResponse(array_map([$this->propertySerializer, 'serialize'], $properties));
    }

    //get all properties
    #[Route('/properties', name: 'get_all_properties', methods: ['GET'])]
    public function getAllProperties(): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Fetch all properties from the database
        $properties = $this->entityManager->getRepository(Property::class)->findAll();

        if (empty($properties)) {
            return $this->json(['message' => 'No properties found'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Serialize and return the properties
        return new JsonResponse(array_map([$this->propertySerializer, 'serialize'], $properties));
    }



    // update a property's field
    #[Route('/property/{id}', name: 'update_property', methods: ['PUT'])]
    public function updateProperty(Request $request, string $id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $property = $this->entityManager->getRepository(Property::class)->find($id);

        if (!$property) {
            return $this->json(['error' => 'Property not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $property->setPropertyType($data['property_type'] ?? $property->getPropertyType());
        $property->setPropertyDescription($data['property_description'] ?? $property->getPropertyDescription());
        $property->setPropertyPrice($data['property_price'] ?? $property->getPropertyPrice());
        $property->setPropertyLocation($data['property_location'] ?? $property->getPropertyLocation());
        $property->setPropertyAge($data['property_age'] ?? $property->getPropertyAge());
        $property->setDatePosted(new \DateTime($data['date_posted'] ?? $property->getDatePosted()->format('Y-m-d H:i:s')));

        $user = $this->entityManager->getRepository(User::class)->find($data['property_owner'] ?? null);
        if ($user) {
            $property->setPropertyOwner($user);
        }

        $this->entityManager->flush();

        return new JsonResponse($this->propertySerializer->serialize($property));
    }

    //delete a property
    #[Route('/property/{id}', name: 'delete_property', methods: ['DELETE'])]
    public function deleteProperty(string $id): JsonResponse
    {
        $property = $this->entityManager->getRepository(Property::class)->find($id);

        if (!$property) {
            return $this->json(['error' => 'Property not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($property);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Property deleted successfully'], JsonResponse::HTTP_NO_CONTENT);
    }

    #[Route('/property/{id}/add-image', name: 'add_image_to_property', methods: ['POST'])]
    public function addImageToProperty(string $id, Request $request): JsonResponse
    {
        $property = $this->entityManager->getRepository(Property::class)->find($id);
        if (!$property) {
            return $this->json(['error' => 'Property not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $image = new Image();
        $image->setImagePath($data['image_path']);
        $property->addImage($image);

        $this->entityManager->persist($property);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Image added successfully'], JsonResponse::HTTP_OK);
    }


    #[Route('/property/{propertyId}/remove-image/{imageId}', name: 'remove_image_from_property', methods: ['DELETE'])]
    public function removeImageFromProperty($propertyId, $imageId): JsonResponse
    {
        $property = $this->entityManager->getRepository(Property::class)->find($propertyId);
        if (!$property) {
            return $this->json(['error' => 'Property not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $image = $this->entityManager->getRepository(Image::class)->find($imageId);
        if (!$image) {
            return $this->json(['error' => 'Image not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $property->removeImage($image);

        $this->entityManager->remove($image);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Image removed successfully'], JsonResponse::HTTP_OK);
    }

}
