<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\JWTService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthController extends AbstractController
{
    private $entityManager;
    private $passwordHasher;
    private $jwtService;


    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, JWTService $jwtService
)
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->jwtService = $jwtService;
    }

    #[Route('/register', name: 'register_user', methods: ['POST'])]
    public function registerUser(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Check if required fields are present
        if (!isset($data['username'], $data['password'], $data['first_name'], $data['last_name'], $data['email'])) {
            return new JsonResponse(['error' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
        }

        $user = new User();
        $user->setUsername($data['username']);
        
        // Hash the password
        $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        $user->setFirstName($data['first_name']);
        $user->setLastName($data['last_name']);
        $user->setEmail($data['email']);
        
        // Set isAdmin to false by default if not provided
        $isAdmin = isset($data['is_admin']) ? $data['is_admin'] : false;
        $user->setAdmin($isAdmin);

        // Optionally set other fields if they are provided
        if (isset($data['phone_number'])) {
            $user->setPhoneNumber($data['phone_number']);
        }
        if (isset($data['date_created'])) {
            $user->setDateCreated(new \DateTime($data['date_created']));
        } else {
            // Set the current date if not provided
            $user->setDateCreated(new \DateTime());
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new JsonResponse(['status' => 'User created'], Response::HTTP_CREATED);
    }

    
   #[Route('/login', name: 'login_user', methods: ['POST'])]
    public function loginUser(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email'], $data['password'])) {
            return new JsonResponse(['error' => 'Email and password are required'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);

        if (!$user || !$this->passwordHasher->isPasswordValid($user, $data['password'])) {
            return new JsonResponse(['error' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
        }

        $payload = [
            'user_id' => $user->getUserId(),
            'email' => $user->getEmail(),
            'is_admin' => $user->isAdmin(),
            'exp' => (new \DateTime('+1 hour'))->getTimestamp(),
        ];

        try {
            $token = $this->jwtService->createToken($payload);

            // Create a response
            $response = new JsonResponse(['message' => 'Logged in successfully']);
            $response->setContent(json_encode(['message' => 'Logged in successfully']));
            $response->headers->set('Content-Type', 'application/json');

            // Set the token in a cookie
            $cookie = new Cookie('auth_token', $token, time() + 3600, '/', null, true, true);
            $response->headers->setCookie($cookie);

            return $response;
        } catch (\RuntimeException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

