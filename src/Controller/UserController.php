<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\UserSerializer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class UserController extends AbstractController
{
    private $entityManager;
    private $passwordHasher;
    private $userSerializer;

    public function __construct(
    EntityManagerInterface $entityManager,
    UserPasswordHasherInterface $passwordHasher, 
    UserSerializer $userSerializer
    )
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->userSerializer = $userSerializer;

    }


    //get all users
    #[Route('/allusers', name: 'get_users', methods: ['GET'])]
    public function getUsers(): JsonResponse
    {
        // Fetch all users from the database
        $userRepository = $this->entityManager->getRepository(User::class);
        $users = $userRepository->findAll();

        // Convert users to an array format
        $userArray = [];
        foreach ($users as $user) {
            $userArray[] = $this->userSerializer->serialize($user);
        }

        // Return the users as a JSON response
        return new JsonResponse($userArray, Response::HTTP_OK);
    }


    //get a single user using their id
   #[Route('/user/{id}', name: 'get_user', methods:['GET'])]
    public function getSingleUser(string $id): JsonResponse
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->find($id);

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($this->userSerializer->serialize($user), Response::HTTP_OK);
    }


    //update a single user using their id
    #[Route('/user/{id}', name: 'update_user', methods: ['PUT'])]
    public function updateUser(Request $request, string $id): JsonResponse
    {
        // Fetch the user from the database
        $user = $this->entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        // Decode request data
        $data = json_decode($request->getContent(), true);

        // Update the user with new data
        if (isset($data['username'])) {
            $user->setUsername($data['username']);
        }
        if (isset($data['password'])) {
            // Hash the new password
            $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);
        }
        if (isset($data['first_name'])) {
            $user->setFirstName($data['first_name']);
        }
        if (isset($data['last_name'])) {
            $user->setLastName($data['last_name']);
        }
        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }
        if (isset($data['phone_number'])) {
            $user->setPhoneNumber($data['phone_number']);
        }
        if (isset($data['is_admin'])) {
            $user->setAdmin($data['is_admin']);
        }        

        // Persist the updated user
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new JsonResponse(['status' => 'User updated'], Response::HTTP_OK);
    }

    //delete a single user
    #[Route('/user/{id}', name: 'delete_user', methods: ['DELETE'])]
    public function deleteUser(string $id): JsonResponse
    {
        // Fetch the user from the database
        $user = $this->entityManager->getRepository(User::class)->find($id);

        // Check if the user was found
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        // Remove the user from the database
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return new JsonResponse(['status' => 'User deleted'], Response::HTTP_OK);
    }

}
